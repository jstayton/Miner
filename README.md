QueryBuilder
============

A dead simple PHP 5 OO interface for building SQL queries. No manual
string concatenation necessary.

Developed by [Justin Stayton](http://twitter.com/jstayton) while at
[Monk Development](http://monkdev.com).

*   [Documentation](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html)
*   [Release Notes](https://github.com/jstayton/QueryBuilder/wiki/Release-Notes)

Requirements
------------

*   PHP >= 5.1.0

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

Examples
--------

### SELECT

    SELECT *
    FROM shows
    INNER JOIN episodes
      ON shows.show_id = episodes.show_id
    WHERE shows.network_id = 12
    ORDER BY episodes.aired_on DESC
    LIMIT 20

As a QueryBuilder:

    $QueryBuilder->select('*')
                 ->from('shows')
                 ->innerJoin('episodes', 'show_id')
                 ->where('shows.network_id', $networkId)
                 ->orderBy('episodes.aired_on', QueryBuilder::ORDER_BY_DESC)
                 ->limit(20);

### INSERT

    INSERT HIGH_PRIORITY shows
    SET network_id = 13,
        name = 'Freaks & Geeks',
        air_day = 'Tuesday'

As a QueryBuilder:

    $QueryBuilder->insert('shows')
                 ->option('HIGH_PRIORITY')
                 ->set('network_id', 13)
                 ->set('name', 'Freaks & Geeks')
                 ->set('air_day', 'Tuesday');

### REPLACE

    REPLACE shows
    SET network_id = 13,
        name = 'Freaks & Geeks',
        air_day = 'Monday'

As a QueryBuilder:

    $QueryBuilder->replace('shows')
                 ->set('network_id', 13)
                 ->set('name', 'Freaks & Geeks')
                 ->set('air_day', 'Monday');

### UPDATE

    UPDATE episodes
    SET aired_on = '2012-06-25'
    WHERE show_id = 12
      OR (name = 'Girlfriends and Boyfriends'
            AND air_day != 'Monday')

As a QueryBuilder:

    $QueryBuilder->update('episodes')
                 ->set('aired_on', '2012-06-25')
                 ->where('show_id', 12)
                 ->openWhere(QueryBuilder::LOGICAL_OR)
                 ->where('name', 'Girlfriends and Boyfriends')
                 ->where('air_day', 'Monday', QueryBuilder::NOT_EQUALS)
                 ->closeWhere();

### DELETE

    DELETE
    FROM shows
    WHERE show_id IN (12, 15, 20)
    LIMIT 3

As a QueryBuilder:

    $QueryBuilder->delete()
                 ->from('shows')
                 ->whereIn('show_id', array(12, 15, 20))
                 ->limit(3);

Methods
-------

*   [__construct](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#__construct)

### SELECT

*   [select](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#select)
*   [getSelectString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getSelectString)
*   [mergeSelectInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeSelectInto)

### INSERT

*   [insert](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#insert)
*   [getInsert](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getInsert)
*   [getInsertString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getInsertString)
*   [mergeInsertInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeInsertInto)

### REPLACE

*   [replace](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#replace)
*   [getReplace](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getReplace)
*   [getReplaceString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getReplaceString)
*   [mergeReplaceInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeReplaceInto)

### UPDATE

*   [update](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#update)
*   [getUpdate](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getUpdate)
*   [getUpdateString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getUpdateString)
*   [mergeUpdateInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeUpdateInto)

### DELETE

*   [delete](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#delete)
*   [getDeleteString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getDeleteString)
*   [mergeDeleteInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeDeleteInto)

### OPTIONS

*   [option](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#option)
*   [calcFoundRows](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#calcFoundRows)
*   [distinct](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#distinct)
*   [getOptionsString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getOptionsString)
*   [mergeOptionsInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeOptionsInto)

### SET

*   [set](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#set)
*   [getSetPlaceholderValues](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getSetPlaceholderValues)
*   [getSetString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getSetString)
*   [mergeSetInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeSetInto)

### FROM

*   [from](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#from)
*   [innerJoin](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#innerJoin)
*   [leftJoin](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#leftJoin)
*   [rightJoin](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#rightJoin)
*   [join](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#join)
*   [getFrom](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getFrom)
*   [getFromAlias](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getFromAlias)
*   [getFromString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getFromString)
*   [getJoinString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getJoinString)
*   [mergeFromInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeFromInto)
*   [mergeJoinInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeJoinInto)

### WHERE

*   [where](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#where)
*   [andWhere](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#andWhere)
*   [orWhere](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#orWhere)
*   [whereIn](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#whereIn)
*   [whereNotIn](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#whereNotIn)
*   [whereBetween](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#whereBetween)
*   [whereNotBetween](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#whereNotBetween)
*   [openWhere](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#openWhere)
*   [closeWhere](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#closeWhere)
*   [getWherePlaceholderValues](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getWherePlaceholderValues)
*   [getWhereString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getWhereString)
*   [mergeWhereInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeWhereInto)

### GROUP BY

*   [groupBy](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#groupBy)
*   [getGroupByString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getGroupByString)
*   [mergeGroupByInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeGroupByInto)

### HAVING

*   [having](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#having)
*   [andHaving](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#andHaving)
*   [orHaving](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#orHaving)
*   [havingIn](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#havingIn)
*   [havingNotIn](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#havingNotIn)
*   [havingBetween](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#havingBetween)
*   [havingNotBetween](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#havingNotBetween)
*   [openHaving](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#openHaving)
*   [closeHaving](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#closeHaving)
*   [getHavingPlaceholderValues](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getHavingPlaceholderValues)
*   [getHavingString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getHavingString)
*   [mergeHavingInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeHavingInto)

### ORDER BY

*   [orderBy](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#orderBy)
*   [getOrderByString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getOrderByString)
*   [mergeOrderByInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeOrderByInto)

### LIMIT

*   [limit](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#limit)
*   [getLimit](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getLimit)
*   [getLimitOffset](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getLimitOffset)
*   [getLimitString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getLimitString)

### Query

*   [query](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#query)
*   [getQueryString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getQueryString)
*   [getPlaceholderValues](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getPlaceholderValues)
*   [isSelect](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#isSelect)
*   [isInsert](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#isInsert)
*   [isReplace](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#isReplace)
*   [isUpdate](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#isUpdate)
*   [isDelete](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#isDelete)
*   [__toString](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#__toString)
*   [mergeInto](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#mergeInto)

### Connection

*   [setPdoConnection](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#setPdoConnection)
*   [getPdoConnection](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getPdoConnection)
*   [setAutoQuote](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#setAutoQuote)
*   [getAutoQuote](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#getAutoQuote)
*   [autoQuote](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#autoQuote)
*   [quote](http://jstayton.github.com/QueryBuilder/classes/QueryBuilder.html#quote)

Feedback
--------

Please open an issue to request a feature or submit a bug report. Or even if
you just want to provide some feedback, I'd love to hear. I'm also available on
Twitter as [@jstayton](http://twitter.com/jstayton).
