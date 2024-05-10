# End-to-end tests for the atoolo suite

This project contains the end-to-end tests for the atoolo suite. Two containers are provided for the tests via [`docker-compose.yaml`](docker-compose.yaml).

1. `solr` - Solr server for the searches
2. `php`- PHP container with Apache web server in which the test project runs

All containers are recreated for each test run. See [`run.sh`](bin/run.sh).

The tests run a Symfony project that uses the Atoolo suite. This project is rebuilt for each test run. See [`install-app.sh`](docker/php/bin/install-app.sh).

The Solr index is also recreated for each test run, using the resources stored in the [`resources`](resources/) directory.

The tests themselves are not executed in the container but on the host system. All tests are stored in the [`tests`](tests/) directory.

## Execute tests

To run the tests, the command `bin/run.sh` must be executed.

## GraphQL tests

The GraphQL tests are stored in the directory [`test/GraphQL`](tests/GraphQL). Only one test class `Test.php` exists here. This test reads all `*.graphql` files below the directory [`test/GraphQL/resources`](test/GraphQL/resources/) and executes them. In addition to the `*.graphql` file, there must always be a `*.result.json` file in which the expected results are stored.

## Debugging

The project against which the tests are executed can also be reached from outside the container via http. For this purpose, the port `9090` is mapped to the host in [`docker-compose.yaml`](docker-compose.yaml). The project runs under the VirtualHost `atoolo-e2e-test`.

The project can be reached with e.g. via curl

```bash
url -H "Host: atoolo-e2e-test" http://localhost:9090
```

The Solr server can also be reached from outside. The port `9091` is mapped to the host. The Solr GUI can be reached with

http://localhost:9091/solr/

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
