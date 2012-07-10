<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xs="http://www.w3.org/2001/XMLSchema" 
	xmlns:fn="http://www.w3.org/2005/xpath-functions"
	exclude-result-prefixes="xs fn"> 

<xsl:output method="xml" indent="yes" encoding="UTF-8"/>


<xsl:template match="/BudgetSpendings">
	<BudgetSpending>
		<xsl:attribute name="startDate">
			<xsl:value-of select="//BudgetSpending[1]/@date"/>
		</xsl:attribute>
		<xsl:attribute name="endDate">
			<xsl:value-of select="//BudgetSpending[last()]/@date"/>
		</xsl:attribute>

		<SpendingSummary>
			<xsl:attribute name="count">
				<xsl:value-of select="sum(//SpendingSummary/@count)"/>
			</xsl:attribute>
			<xsl:attribute name="fullAmount">
				<xsl:value-of select="sum(//SpendingSummary/@fullAmount)"/>
			</xsl:attribute>
			<Sebra>
				<xsl:for-each select="//Sebra/Payment[not(preceding::Sebra/Payment/@name=./@name)]">
					<xsl:sort select="@code"/>
					<xsl:sort select="@name" case-order="upper-first"/>
					<xsl:variable name="currentName" select="@name"/>
					<Payment>
						<xsl:if test="@code">
							<xsl:attribute name="code">
								<xsl:value-of select="@code"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="name">
							<xsl:value-of select="@name"/>
						</xsl:attribute>
						<xsl:attribute name="count">
							<xsl:value-of select="sum(//Sebra/Payment[@name=$currentName]/@count)"/>
						</xsl:attribute>
						<xsl:attribute name="amount">
							<xsl:value-of select="sum(//Sebra/Payment[@name=$currentName]/@amount)"/>
						</xsl:attribute>
					</Payment>
				</xsl:for-each>
			</Sebra>
			<Other>
				<xsl:for-each select="//Other/Payment[not(preceding::Other/Payment/@name=./@name)]">
					<xsl:sort select="@code"/>
					<xsl:sort select="@name" case-order="upper-first"/>
					<xsl:variable name="currentName" select="@name"/>
					<Payment>
						<xsl:if test="@code">
							<xsl:attribute name="code">
								<xsl:value-of select="@code"/>
							</xsl:attribute>
						</xsl:if>
						<xsl:attribute name="name">
							<xsl:value-of select="@name"/>
						</xsl:attribute>
						<xsl:attribute name="count">
							<xsl:value-of select="sum(//Other/Payment[@name=$currentName]/@count)"/>
						</xsl:attribute>
						<xsl:attribute name="amount">
							<xsl:value-of select="sum(//Other/Payment[@name=$currentName]/@amount)"/>
						</xsl:attribute>
					</Payment>
				</xsl:for-each>
			</Other>
		</SpendingSummary>
		<SpendingBreakdown>
			<xsl:for-each select="//SpendingBreakdown/SpendingEntity[not(following::SpendingBreakdown/SpendingEntity/@code=./@code)]">
				<xsl:sort select="@code"/>
				<xsl:sort select="@name" case-order="upper-first"/>
				<xsl:variable name="currentCode" select="@code"/>
				<SpendingEntity>
					<xsl:if test="@code">
						<xsl:attribute name="code">
							<xsl:value-of select="@code"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:attribute name="name">
						<xsl:value-of select="@name"/>
					</xsl:attribute>
					<xsl:attribute name="count">
						<xsl:value-of select="sum(//SpendingBreakdown/SpendingEntity[@code=$currentCode]/@count)"/>
					</xsl:attribute>
					<xsl:attribute name="fullAmount">
						<xsl:value-of select="sum(//SpendingBreakdown/SpendingEntity[@code=$currentCode]/@fullAmount)"/>
					</xsl:attribute>

					<xsl:for-each select="//SpendingBreakdown/SpendingEntity[@code=$currentCode]/Payment[not(following::SpendingEntity[@code=$currentCode]/Payment/@name=./@name)]">
						<xsl:sort select="@code"/>
						<xsl:sort select="@name" case-order="upper-first"/>
						<xsl:variable name="currentName" select="@name"/>
						<Payment>
							<xsl:if test="@code">
								<xsl:attribute name="code">
									<xsl:value-of select="@code"/>
								</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="name">
								<xsl:value-of select="@name"/>
							</xsl:attribute>

							<xsl:for-each select="//SpendingEntity[@code=$currentCode]/Payment[@name=$currentName]">
								<PaymentDay>
									<xsl:attribute name="date">
										<xsl:value-of select="ancestor::BudgetSpending/@date"/>
									</xsl:attribute>
									<xsl:attribute name="count">
										<xsl:value-of select="@count"/>
									</xsl:attribute>
									<xsl:attribute name="amount">
										<xsl:value-of select="@amount"/>
									</xsl:attribute>
								</PaymentDay>
							</xsl:for-each>
						</Payment>		
					</xsl:for-each>
				</SpendingEntity>
			</xsl:for-each>
		</SpendingBreakdown>
	</BudgetSpending>
</xsl:template>

</xsl:stylesheet>

