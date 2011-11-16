QueryBuilder
============

A dead simple PHP 5 OO interface for building SQL queries. No manual
string concatenation necessary.

Developed by [Justin Stayton](http://twitter.com/jstayton) while at
[Monk Development](http://monkdev.com).

*   [Documentation](http://jstayton.github.com/QueryBuilder)
*   [Release Notes](https://github.com/jstayton/QueryBuilder/wiki/Release-Notes)

Requirements
------------

*   PHP 5.1.0 or newer.

Getting Started
---------------

To start, make sure to add the class to your autoloader or require it directly:

    require "QueryBuilder.php";

Composing a query with QueryBuilder is very similar to writing the SQL by hand,
as many of the directives map directly to methods:

    $QueryBuilder = new QueryBuilder();
    $QueryBuilder->select('*')
                 ->from('shows')
                 ->innerJoin('episodes', 'show_id')
                 ->where('shows.network_id', $networkId)
                 ->orderBy('episodes.aired_on', QueryBuilder::ORDER_BY_DESC)
                 ->limit(20);

Now that the query is built,

    $QueryBuilder->getQueryString();

returns the full SQL query string with placeholders (?), and

    $QueryBuilder->getPlaceholderValues();

returns the array of placeholder values that can then be passed to your
database connection or abstraction layer of choice. Or, if you'd prefer it all
at once, you can get the query string with values already safely quoted:

    $QueryBuilder->getQueryString(false);

If you're using PDO, however, QueryBuilder makes executing the query even
easier:

    $PDOStatement = $QueryBuilder->query();

QueryBuilder works directly with your PDO connection, which can be passed
during creation of the QueryBuilder object

    $QueryBuilder = new QueryBuilder($PDO);

or after

    $QueryBuilder->setPdoConnection($PDO);

Methods
-------

### SELECT

*   [select](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodselect)
*   [calcFoundRows](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodcalcFoundRows)
*   [distinct](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methoddistinct)
*   [option](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodoption)
*   [getSelectString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetSelectString)
*   [mergeSelectInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeSelectInto)

### FROM

*   [from](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodfrom)
*   [innerJoin](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodinnerJoin)
*   [leftJoin](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodleftJoin)
*   [rightJoin](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodrightJoin)
*   [join](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodjoin)
*   [getFrom](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetFrom)
*   [getFromAlias](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetFromAlias)
*   [getFromString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetFromString)
*   [getJoinString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetJoinString)
*   [mergeJoinInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeJoinInto)

### WHERE

*   [where](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodwhere)
*   [andWhere](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodandWhere)
*   [orWhere](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodorWhere)
*   [whereIn](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodwhereIn)
*   [whereNotIn](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodwhereNotIn)
*   [whereBetween](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodwhereBetween)
*   [whereNotBetween](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodwhereNotBetween)
*   [openWhere](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodopenWhere)
*   [closeWhere](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodcloseWhere)
*   [getWherePlaceholderValues](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetWherePlaceholderValues)
*   [getWhereString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetWhereString)
*   [mergeWhereInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeWhereInto)

### GROUP BY

*   [groupBy](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgroupBy)
*   [getGroupByString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetGroupByString)
*   [mergeGroupByInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeGroupByInto)

### HAVING

*   [having](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodhaving)
*   [andHaving](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodandHaving)
*   [orHaving](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodorHaving)
*   [havingIn](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodhavingIn)
*   [havingNotIn](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodhavingNotIn)
*   [havingBetween](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodhavingBetween)
*   [havingNotBetween](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodhavingNotBetween)
*   [openHaving](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodopenHaving)
*   [closeHaving](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodcloseHaving)
*   [getHavingPlaceholderValues](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetHavingPlaceholderValues)
*   [getHavingString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetHavingString)
*   [mergeHavingInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeHavingInto)

### ORDER BY

*   [orderBy](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodorderBy)
*   [getOrderByString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetOrderByString)
*   [mergeOrderByInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeOrderByInto)

### LIMIT

*   [limit](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodlimit)
*   [getLimit](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetLimit)
*   [getLimitOffset](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetLimitOffset)
*   [getLimitString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetLimitString)

### Query

*   [query](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodquery)
*   [getQueryString](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetQueryString)
*   [getPlaceholderValues](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetPlaceholderValues)
*   [__tostring](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#method__toString)
*   [mergeInto](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodmergeInto)

### Utility

*   [setPdoConnection](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodsetPdoConnection)
*   [getPdoConnection](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodgetPdoConnection)
*   [quote](http://jstayton.github.com/QueryBuilder/QueryBuilder/QueryBuilder.html#methodquote)

Feedback
--------

Please open an issue to request a feature or submit a bug report. Or even if
you just want to provide some feedback, I'd love to hear. I'm also available on
Twitter as [@jstayton](http://twitter.com/jstayton).
