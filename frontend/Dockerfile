FROM node:24-bookworm-slim

WORKDIR /app

RUN chown node:node /app

COPY --chown=node:node package.json package-lock.json ./

USER node

RUN npm ci --ignore-scripts --no-audit

USER root

COPY --chmod=755 docker/entrypoint.sh /usr/local/bin/frontend-entrypoint

USER node

EXPOSE 5173

ENTRYPOINT ["frontend-entrypoint"]
