
## Debugging

In the php container `xdebug` is set up. The application path in the container is `/apps/atoolo-e2e-test`. The application can be reached via port 9090 (if not changed in `docker-compose.yaml`) via the host `atoolo-e2e-test`.

xdebug can be activated by setting the environment variables `XDEBUG_MODE` and `XDEBUG_SESSION` in `docker-compose.yaml` for the `php` service.

```yaml
php:
   environment:
      XDEBUG_MODE: debug,develop,profile
      XDEBUG_SESSION: PHPSTORM
```

### PHPStrom

To set up debugging with PHPStorm, a new server with the host `atoolo-e2e-test` and the port `9090` must be set up in the settings for an Atoolo project (e.g. `atoolo/graphql-search-bundle`) in `PHP / Servers`. The path mapping must be activated. And e.g. the path `/home/user/git/atoolo-graph-serach-bundle` must be mapped to the server path `/apps/atoolo-e2e-test/vendor/atoolo/graphql-search-bundle`.