require('dotenv').config();

const express = require('express');
const { Cluster } = require('puppeteer-cluster');
const randomUserAgent = require('random-user-agent');
const v8 = require('v8');
const app = express();

const options = {
  port: parseInt(process.env.PORT || 8080),
  maxConcurrency: parseInt(process.env.MAX_BROWSERS || '1'),
  executablePath: process.env.CHROMIUM_PATH || '/usr/bin/google-chrome-stable',
  args: process.env.CHROMIUM_ARGS ? process.env.CHROMIUM_ARGS.split(' ') : ['--no-sandbox', '--disable-web-security'],
  ignoreHTTPSErrors: parseInt(process.env.IGNORE_HTTPS_ERRORS || '1'),
  monitor: parseInt(process.env.DEBUG || '0'),
  defaultTimeout: parseInt(process.env.DEFAULT_TIMEOUT || '30'),
};

app.use((err, req, res, next) => {
  next(err);
});

app.use('/healthcheck', require('express-healthcheck')());

app.use('/usage', (req, res) => {
  let { rss, heapTotal, external, arrayBuffers } = process.memoryUsage();

  let totalSize = v8.getHeapStatistics().total_available_size;
  let usedSize = rss + heapTotal + external + arrayBuffers;
  let freeSize = totalSize - usedSize;
  let percentUsage = (usedSize / totalSize) * 100;

  return res.json({
    memory: {
      free: freeSize,
      used: usedSize,
      total: totalSize,
      percent: percentUsage,
    },
  });
});

(async () => {
  const cluster = await Cluster.launch({
    concurrency: Cluster.CONCURRENCY_CONTEXT,
    maxConcurrency: options.maxConcurrency,
    puppeteerOptions: {
      executablePath: options.executablePath,
      ignoreHTTPSErrors: options.ignoreHTTPSErrors,
      args: options.args,
    },
    monitor: options.monitor,
  });

  await cluster.task(async ({ page, data: query }) => {
    const triggeredRequests = [];
    const consoleLines = [];
    const actions = JSON.parse(query.actions || null);

    // Set the viewport by default as 1920x1080
    await page.setViewport({
      width: 1920,
      height: 1080,
    });

    // If ?viewport=[width]x[height] is present,
    // use the passed viewport.
    if (query.viewport) {
      const [width, height] = query.viewport.split('x');

      await page.setViewport({
        width: parseInt(width),
        height: parseInt(height),
      });
    }

    // Set the user agent randomly, based on the device, if existent.
    // Otherwise, set it default to desktop.
    await page.setUserAgent(
      randomUserAgent(query.device ? query.device.toLowerCase() : 'desktop')
    );

    // If ?user_agent= is set, use the passed User-Agent
    if (query.user_agent) {
      await page.setUserAgent(query.user_agent);
    }

    // If extra HTTP headers are set, apply them.
    if (query.extra_headers) {
      await page.setExtraHTTPHeaders(
        JSON.parse(query.extra_headers)
      );
    }

    await page.setRequestInterception(true);

    if (query.console_lines) {
      page.on('console', line => {
        consoleLines.push({
          type: line.type(),
          content: line.text(),
          location: line.location(),
        });
      });
    }


    page.on('request', request => {
      // Allow to block certain extensions.
      // For example: ?blocked_extensions=.png,.jpg
      if (query.blocked_extensions) {
        // Example:
        // [
        //   /\.jpg$/, /\.jpeg$/, /\.png$/, /\.gif$/,/\.css$/, /\.css\?/, /fonts/, /font/,
        // ]

        const blockedExtensions = query.blocked_extensions
          .split(',')
          .map(pattern => new RegExp(`${pattern}$`));

        let shouldBlockExtension = blockedExtensions.filter(regex => {
          return regex.test(request.url());
        }).length > 0;

        if (shouldBlockExtension) {
          return request.abort();
        }
      }

      // Allow to block certain resource types.
      // For example: ?blocked_resource_types=image,media
      if (query.blocked_resource_types) {
        const blockedResourceTypes= query.blocked_resource_types.split(',');

        if (blockedResourceTypes.includes(request.resourceType())) {
          return request.abort();
        }
      }

      if (query.triggered_requests) {
        triggeredRequests.push({
          type: request.resourceType(),
          method: request.method(),
          url: request.url(),
          headers: request.headers(),
          post_data: request.postData() || '',
          chain: request.redirectChain().map(req => req.url()),
          from_navigation: request.isNavigationRequest(),
        });
      }

      return request.continue();
    });

    const crawledPage = await page.goto(query.url, {
      waitUntil: query.until_idle ? 'networkidle0' : 'networkidle2',
      timeout: query.timeout ? query.timeout * 1000 : options.defaultTimeout * 1000,
    });

    // The .reduce() function is used as a workaround to
    // make sure that each step gets called in right order and
    // is waited for it. Please see the following post on SO:
    // https://stackoverflow.com/a/49499491/3704404

    await actions.reduce(async (promise, action) => {
      await promise;

      if (action.name === 'click') {
        await page.click(action.selector, {
          clickCount: action.amount,
          delay: 100,
          button: action.button,
        });
      }

      if (action.name === 'type') {
        await page.type(action.selector, action.text, {
          delay: action.delay,
        });
      }

      if (action.name === 'down') {
        await page.keyboard.down(action.text);
      }

      if (action.name === 'up') {
        await page.keyboard.up(action.text);
      }

      if (action.name === 'press') {
        await page.keyboard.press(action.text, {
          delay: action.delay,
        });
      }

      if (action.name === 'wait') {
        await page.waitForTimeout(action.seconds * 1000);
      }

      if (action.name === 'wait-for-selector') {
        await page.waitForSelector(action.selector, { timeout: action.seconds * 1000 });
      }
    }, Promise.resolve());


    const screenshot = query.screenshot ? await (async function () {
      return await page.screenshot({
        type: 'jpeg',
        quality: parseInt(query.quality || 75),
        fullPage: true,
        encoding: 'base64',
      });
    })() : null;

    const html = query.html ? await page.evaluate(() => document.documentElement.innerHTML) : '';

    const cookies = query.cookies ? (await page._client.send('Network.getAllCookies')).cookies : [];

    return {
      status: crawledPage.status(),
      triggered_requests: triggeredRequests,
      console_lines: consoleLines,
      cookies,
      html,
      screenshot,
      actions,
    }
  });

  app.get('/', async (req, res) => {
    try {
      const data = await cluster.execute(req.query);

      return res.status(200).json({ data });
    } catch (err) {
      return res
        .status(200)
        .json({
          data: {
            status: 500,
            triggered_requests: [],
            console_lines: [],
            cookies: [],
            html: '',
            screenshot: null,
            actions: [],
          },
        });
    }
  });

  const server = app.listen(options.port, () => {
    console.log(`Clusteer server running on port ${options.port}.`)
    console.log(`Options: `, options);
  });

  // Make sure the app responds to SIGTERM and SIGINT so
  // it closes the node server.js process.
  process.on('SIGTERM', () => {
    server.close();
    process.exit();
  });

  process.on('SIGINT', () => {
    server.close();
    process.exit();
  });
})();
