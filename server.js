require('dotenv').config();

const express = require('express');
const { Cluster } = require('puppeteer-cluster');
const randomUserAgent = require('random-user-agent');
const app = express();

const options = {
  port: parseInt(process.env.PORT || 8080),
  maxConcurrency: parseInt(process.env.MAX_BROWSERS || 1),
  executablePath: process.env.CHROMIUM_PATH || '/usr/bin/google-chromne-stable',
  args: process.env.CHROMIUM_ARGS ? process.env.CHROMIUM_ARGS.split(' ') : ['--no-sandbox', '--disable-web-security'],
  ignoreHTTPSErrors: parseInt(process.env.IGNORE_HTTPS_ERRORS || true),
  monitor: parseInt(process.env.DEBUG || false),
  defaultTimeout: parseInt(process.env.DEFAULT_TIMEOUT || 30),
};

app.use((err, req, res, next) => {
  next(err);
});

app.use('/healthcheck', require('express-healthcheck')());

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

    page.on('console', line => {
      consoleLines.push({
        type: line.type(),
        content: line.text(),
        location: line.location(),
      });
    });

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

      triggeredRequests.push({
        type: request.resourceType(),
        method: request.method(),
        url: request.url(),
        headers: request.headers(),
        post_data: request.postData() || '',
        chain: request.redirectChain().map(req => req.url()),
        from_navigation: request.isNavigationRequest(),
      });

      return request.continue();
    });

    const crawledPage = await page.goto(query.url, {
      waitUntil: query.until_idle ? 'networkidle0' : 'networkidle2',
      timeout: query.timeout ? query.timeout * 1000 : options.defaultTimeout * 1000,
    });

    const html = await page.evaluate(() => document.documentElement.innerHTML);

    return {
      status: crawledPage.status(),
      triggered_requests: query.triggered_requests ? triggeredRequests : [],
      console_lines: query.console_lines ? consoleLines : [],
      cookies: query.cookies ? (await page._client.send('Network.getAllCookies')).cookies : [],
      html: query.html ? html : '',
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
