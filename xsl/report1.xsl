<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xs="http://www.w3.org/2001/XMLSchema" 
	xmlns:fn="http://www.w3.org/2005/xpath-functions"
	exclude-result-prefixes="xs fn"> 

<xsl:output method="text" encoding="UTF-8"/>

<xsl:template match="/BudgetSpending">
	<xsl:text>{"name":"Bulgarian Budget Spending","children":[</xsl:text>
	<xsl:apply-templates select="//SpendingEntity"/>
	<xsl:text>]}</xsl:text>
</xsl:template>

<xsl:template match="SpendingEntity">
	<xsl:if test="position()>1">
		<xsl:text>,</xsl:text>
	</xsl:if>
	<xsl:text>{"name":"</xsl:text>
	<xsl:value-of select="translate(@name,'&quot;','')"/>
	<xsl:text>","children":[</xsl:text>
	<xsl:apply-templates select="Payment"/>
	<xsl:text>]}</xsl:text>
</xsl:template>

<xsl:template match="Payment[sum(PaymentDay/@amount)>0]">
	<node>
		<xsl:if test="position()>1">
			<xsl:text>,</xsl:text>
		</xsl:if>
		<xsl:text>{"name":"</xsl:text>
		<xsl:value-of select="translate(@name,'&quot;','\&quot;')"/>
		<xsl:text>","count":</xsl:text>
		<xsl:value-of select="sum(PaymentDay/@count)"/>
		<xsl:text>,"amount":</xsl:text>
		<xsl:value-of select="sum(PaymentDay/@amount)"/>
		<xsl:text>}</xsl:text>
	</node>
</xsl:template>

</xsl:stylesheet>

