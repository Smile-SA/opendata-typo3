<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns="http://www.w3.org/1999/xhtml"
>
	<xsl:output method="xml" indent="yes" encoding="utf-8" omit-xml-declaration="yes" />

	<xsl:template match="/">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="command">
		<h2>
			<xsl:value-of select="@cmd"/>
			:
			<xsl:value-of select="@name"/>
		</h2>
		<xsl:if test="@brief != ''">
			<p class="brief">
				<xsl:value-of select="@brief"/>
			</p>
		</xsl:if>
		<xsl:apply-templates select="parameters" />
		<xsl:apply-templates select="description" />
	</xsl:template>

	<xsl:template match="parameters">
		<xsl:param name="level">0</xsl:param>
		<xsl:param name="value" />
		<xsl:if test="$level &lt; 4">
			<div>
				<xsl:attribute name="class">level<xsl:value-of select="$level"/></xsl:attribute>
				<xsl:call-template name="paramtable">
					<xsl:with-param name="level" select="$level" />
				</xsl:call-template>
				<xsl:if test="count(parameter[@type = 'enum']/values/value) > 0">
					<xsl:call-template name="enumtable">
						<xsl:with-param name="level" select="$level" />
					</xsl:call-template>
					<xsl:call-template name="enumtree">
						<xsl:with-param name="level" select="$level" />
					</xsl:call-template>
				</xsl:if>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template name="paramtable">
		<xsl:param name="level" />
		<table class="parameters">
			<caption>Parameters</caption>
			<thead>
				<tr>
					<th class="nameheader">
						Name
					</th>
					<th class="typeheader">
						Type
					</th>
					<th class="mandatoryheader">
						Required
					</th>
					<th class="defaultheader">
						Default
					</th>
					<th class="descheader">
						Description
					</th>
				</tr>
			</thead>
			<tbody>
				<xsl:apply-templates select="parameter" />
			</tbody>
		</table>
	</xsl:template>

	<xsl:template match="parameter">
		<tr>
			<th class="namecell">
				<xsl:value-of select="@name"/>
			</th>
			<td class="typecell">
				<xsl:choose>
					<xsl:when test="@type = 'enum'">
						Enumeration
					</xsl:when>
					<xsl:when test="@type = 'string'">
						String
					</xsl:when>
					<xsl:when test="@type = 'number'">
						Number
					</xsl:when>
				</xsl:choose>
			</td>
			<td class="mandatorycell">
				<xsl:choose>
					<xsl:when test="@mandatory = '1'">
						Yes
					</xsl:when>
					<xsl:otherwise>
						No
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="defaultcell">
				<xsl:value-of select="@default" />
			</td>
			<td class="desccell">
				<xsl:value-of select="description/text()" />
			</td>
		</tr>
	</xsl:template>

	<xsl:template name="enumtable">
		<xsl:param name="level" />
		<table class="enums">
			<caption>
				Enumeration values
			</caption>
			<thead>
				<tr>
					<th class="paramheader">
						Parameter
					</th>
					<th class="valueheader">
						Value
					</th>
					<th class="subparamheader">
						Has subparams
					</th>
					<th class="descheader">
						Description
					</th>
				</tr>
			</thead>
			<tbody>
				<xsl:for-each select="parameter[@type = 'enum']">
					<xsl:for-each select="values/value">
						<xsl:call-template name="makeenumrows" />
					</xsl:for-each>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>

	<xsl:template name="makeenumrows">
		<tr>
			<xsl:if test="position() = 1">
				<th class="paramcell">
					<xsl:attribute name="rowspan">
						<xsl:value-of select="count(../value)" />
					</xsl:attribute>
					<xsl:value-of select="../../@name"/>
				</th>
			</xsl:if>
			<td class="valuecell">
				<xsl:value-of select="@value"/>
			</td>
			<td class="subparamscell">
				<xsl:choose>
					<xsl:when test="count(parameters/parameter) > 0">
						Yes
					</xsl:when>
					<xsl:otherwise>
						No
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td class="desccell">
				<xsl:value-of select="description/text()"/>
			</td>
		</tr>
	</xsl:template>

	<xsl:template name="enumtree">
		<xsl:param name="level" />
		<xsl:for-each select="parameter[@type = 'enum']/values/value/parameters">
			<xsl:call-template name="makeenumpart">
				<xsl:with-param name="level" select="$level" />
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="makeenumpart">
		<xsl:param name="level" />
		<xsl:variable name="header">h<xsl:value-of select="$level + 3"/></xsl:variable>
		<xsl:element name="{$header}">
			<xsl:value-of select="../../../@name"/>
			:
			<xsl:value-of select="../@value" />
		</xsl:element>
		<xsl:apply-templates select=".">
			<xsl:with-param name="level" select="$level + 1" />
		</xsl:apply-templates>
	</xsl:template>

	<xsl:template match="description">
		<div class="description">
			<xsl:value-of select="text()" disable-output-escaping="yes" />
		</div>
	</xsl:template>

</xsl:stylesheet>
