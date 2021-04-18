ARG VERSION=lts

FROM node:$VERSION

LABEL maintainer="Renoki Co. <alex@renoki.org>"

COPY server.js package.json /app/

RUN sudo apt-get update && \
    sudo apt-get install -y libnss3 chromium-browser && \
    cd /app && \
    npm install

WORKDIR /app

ENV PORT=8080
ENV CHROMIUM_PATH=/usr/bin/chromium-browser

EXPOSE 8080

ENTRYPOINT ["node", "/app/server.js"]
