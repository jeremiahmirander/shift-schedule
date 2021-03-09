

# Dev Server
`docker-compose up -d`

`docker-compose exec php composer dev-setup`

Server accessible from http://localhost:8080

## Debugging

xDebug makes everything go a lot slower, so leave it off if you're not using it

Enabled xDebug `docker-compose exec php composer xon`

Disable xDebug `docker-compose exec php composer xoff`

With `/server/.vscode/launch.json` and `/server/xdebug.conf` you can set your configuration, just make sure the ports match

Changing `/server/xdebug.conf` will require you to rebuild your docker image

## Code Coverage

xDebug is required for running code coverage `docker-compose exec php composer xon`

HTML report `docker-compose exec php composer coverage-html`

Report is visible from `/server/tests/coverage/index.html`

Don't forget to turn xDebug off when you're done `docker-compose exec php composer xoff`

# Production Server
`docker-compose up -f ./docker-compose.prod.yaml -d`

`docker-compose exec php composer setup`
