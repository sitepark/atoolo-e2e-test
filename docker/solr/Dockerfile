FROM solr:9

# The index should be created again and again for the tests.
# Therefore the index should not be persistent, but stored in the container.
RUN mkdir /var/solr/data

USER root
# See: https://solr.apache.org/guide/solr/latest/query-guide/spatial-search.html#jts-and-polygons-flat
RUN wget -P /opt/solr-*/server/solr-webapp/webapp/WEB-INF/lib   https://repo1.maven.org/maven2/org/locationtech/jts/jts-core/1.17.1/jts-core-1.17.1.jar
USER $SOLR_UID

