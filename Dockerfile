ARG VERSION=lts

FROM node:$VERSION

LABEL maintainer="Renoki Co. <alex@renoki.org>"

COPY server.js package.json /app/

RUN apk add --no-cache --update chromium && \
    cd /app && \
    npm install && \
    npm install modclean -g && \
    rm -rf node_modules/*/test/ node_modules/*/tests/ && \
    npm prune && \
    modclean -n default:safe --run && \
    npm uninstall -g modclean && \
    /usr/bin/chromium-browser --version

WORKDIR /app

ENV PORT=8080
ENV CHROMIUM_PATH=/usr/bin/chromium-browser

EXPOSE 8080

ENTRYPOINT ["node", "/app/server.js"]
