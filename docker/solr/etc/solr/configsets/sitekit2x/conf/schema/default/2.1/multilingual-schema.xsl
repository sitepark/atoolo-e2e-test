<?xml version="1.0"?>


<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
		<xsl:output omit-xml-declaration="yes" indent="yes"/>
		<xsl:strip-space elements="*"/>

		<xsl:param name="outputDir"/>

		<xsl:template match="node()|@*">
				<xsl:copy>
						<xsl:apply-templates select="node()|@*"/>
				</xsl:copy>
		</xsl:template>

		<xsl:template match="multilingual">
			<fields>
				<xsl:for-each select="./*">

					<xsl:variable name="filename">
						<xsl:choose>
							<xsl:when test="./@filename"><xsl:value-of select="./@filename"/></xsl:when>
							<xsl:otherwise>schema-<xsl:value-of select="name(.)"/>.xml</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>

					<xsl:variable name="schemaname">
						<xsl:choose>
							<xsl:when test="./@schemaname"><xsl:value-of select="./@schemaname"/></xsl:when>
							<xsl:otherwise>sitepark-2.1-<xsl:value-of select="name(.)"/></xsl:otherwise>
						</xsl:choose>
					</xsl:variable>

					<xsl:result-document method="xml" href="{$outputDir}/{$filename}">

						<xsl:variable name="fieldType">
							<xsl:choose>
								<xsl:when test="./@multilingualType"><xsl:value-of select="./@multilingualType"/></xsl:when>
								<xsl:otherwise>text_<xsl:value-of select="name(.)"/></xsl:otherwise>
							</xsl:choose>
						</xsl:variable>

						<schema name="{$schemaname}" version="1.6">
							<uniqueKey>id</uniqueKey>
							<xi:include href="../../schema-types.xml" xmlns:xi="http://www.w3.org/2001/XInclude"/>
							<fields>
								<xsl:copy-of select="document('schema-fields-general.xml')/fields/*"/>

								<field name="title"       type="{$fieldType}" stored="true" indexed="true" multiValued="false" termVectors="true"/>
								<field name="description" type="{$fieldType}" stored="true" indexed="true" multiValued="true"  termVectors="true"/>
								<field name="content"     type="{$fieldType}" stored="true" indexed="true" multiValued="true"  termVectors="true"/>
								<field name="sp_title"    type="{$fieldType}" stored="true" indexed="true" />
								<field name="sp_intro"    type="{$fieldType}" stored="true" indexed="true" />

								<dynamicField name="sp_meta_text_*" type="{$fieldType}" stored="true" indexed="true"  multiValued="true"/>
								<dynamicField name="sp_meta_single_text_*" type="{$fieldType}" stored="true" indexed="true"  multiValued="false"/>

							</fields>
						</schema>
					</xsl:result-document>

				</xsl:for-each>
			</fields>
		</xsl:template>

</xsl:stylesheet>
