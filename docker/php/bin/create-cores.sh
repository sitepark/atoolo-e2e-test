#!/bin/bash
set -e

echo -n 'wait until solr has started ... '

until [ \
  "$(curl -s -o /dev/null -w "%{http_code}" "http://$SOLR_HOST:$SOLR_PORT/api/cores")" \
  -eq 200 ]
do
  sleep 0.2
done

echo started

echo create solr core: www
curl -X POST http://$SOLR_HOST:$SOLR_PORT/api/cores -H 'Content-Type: application/json' -d '
  {
    "create": {
      "name": "www",
      "configSet": "sitekit2x", "schema" : "conf/schema/default/2.1/schema.xml"
    }
  }
'

echo create solr core: www-en_US
curl -X POST http://$SOLR_HOST:$SOLR_PORT/api/cores -H 'Content-Type: application/json' -d '
  {
    "create": {
      "name": "www-en_US",
      "configSet": "sitekit2x", "schema" : "conf/schema/default/2.1/schema-en.xml"
    }
  }
'

