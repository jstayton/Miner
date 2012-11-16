Miner
=====

A dead simple PHP class for building SQL statements. No manual string
concatenation necessary.

Developed by [Justin Stayton](http://twitter.com/jstayton) while at
[Monk Development](http://monkdev.com).

*   [Documentation](http://jstayton.github.com/Miner/classes/Miner.html)
*   [Release Notes](https://github.com/jstayton/Miner/wiki/Release-Notes)

Requirements
------------

*   PHP >= 5.1.0

Installation
------------

### Composer

The recommended installation method is through
[Composer](http://getcomposer.org/), a dependency manager for PHP. Just add
`jstayton/miner` to your project's `composer.json` file:

    {
        "require": {
            "jstayton/miner": "*"
        }
    }

[More details](http://packagist.org/packages/jstayton/miner) can be found over
at [Packagist](http://packagist.org).

### Manually

1.  Copy `src/Miner.php` to your codebase, perhaps to the `vendor` directory.
2.  Add the `Miner` class to your autoloader or `require` the file directly.

Getting Started
---------------

Composing SQL with Miner is very similar to writing it by hand, as much of the
syntax maps directly to methods:

    $Miner = new Miner();
    $Miner->select('*')
          ->from('shows')
          ->innerJoin('episodes', 'show_id')
          ->where('shows.network_id', $networkId)
          ->orderBy('episodes.aired_on', Miner::ORDER_BY_DESC)
          ->limit(20);

Now that the statement is built,

    $Miner->getStatement();

returns the full SQL string with placeholders (?), and

    $Miner->getPlaceholderValues();

returns the array of placeholder values that can then be passed to your
database connection or abstraction layer of choice. Or, if you'd prefer it all
at once, you can get the SQL string with values already safely quoted:

    $Miner->getStatement(false);

If you're using PDO, however, Miner makes executing the statement even easier:

    $PDOStatement = $Miner->execute();

Miner works directly with your PDO connection, which can be passed during
creation of the Miner object

    $Miner = new Miner($PDO);

or after

    $Miner->setPdoConnection($PDO);

Usage
-----

### SELECT

    SELECT *
    FROM shows
    INNER JOIN episodes
      ON shows.show_id = episodes.show_id
    WHERE shows.network_id = 12
    ORDER BY episodes.aired_on DESC
    LIMIT 20

With Miner:

    $Miner->select('*')
          ->from('shows')
          ->innerJoin('episodes', 'show_id')
          ->where('shows.network_id', $networkId)
          ->orderBy('episodes.aired_on', Miner::ORDER_BY_DESC)
          ->limit(20);

### INSERT

    INSERT HIGH_PRIORITY shows
    SET network_id = 13,
        name = 'Freaks & Geeks',
        air_day = 'Tuesday'

With Miner:

    $Miner->insert('shows')
          ->option('HIGH_PRIORITY')
          ->set('network_id', 13)
          ->set('name', 'Freaks & Geeks')
          ->set('air_day', 'Tuesday');

### REPLACE

    REPLACE shows
    SET network_id = 13,
        name = 'Freaks & Geeks',
        air_day = 'Monday'

With Miner:

    $Miner->replace('shows')
          ->set('network_id', 13)
          ->set('name', 'Freaks & Geeks')
          ->set('air_day', 'Monday');

### UPDATE

    UPDATE episodes
    SET aired_on = '2012-06-25'
    WHERE show_id = 12
      OR (name = 'Girlfriends and Boyfriends'
            AND air_day != 'Monday')

With Miner:

    $Miner->update('episodes')
          ->set('aired_on', '2012-06-25')
          ->where('show_id', 12)
          ->openWhere(Miner::LOGICAL_OR)
          ->where('name', 'Girlfriends and Boyfriends')
          ->where('air_day', 'Monday', Miner::NOT_EQUALS)
          ->closeWhere();

### DELETE

    DELETE
    FROM shows
    WHERE show_id IN (12, 15, 20)
    LIMIT 3

With Miner:

    $Miner->delete()
          ->from('shows')
          ->whereIn('show_id', array(12, 15, 20))
          ->limit(3);

Methods
-------

*   [__construct](http://jstayton.github.com/Miner/classes/Miner.html#__construct)

### SELECT

*   [select](http://jstayton.github.com/Miner/classes/Miner.html#select)
*   [getSelectString](http://jstayton.github.com/Miner/classes/Miner.html#getSelectString)
*   [mergeSelectInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeSelectInto)

### INSERT

*   [insert](http://jstayton.github.com/Miner/classes/Miner.html#insert)
*   [getInsert](http://jstayton.github.com/Miner/classes/Miner.html#getInsert)
*   [getInsertString](http://jstayton.github.com/Miner/classes/Miner.html#getInsertString)
*   [mergeInsertInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeInsertInto)

### REPLACE

*   [replace](http://jstayton.github.com/Miner/classes/Miner.html#replace)
*   [getReplace](http://jstayton.github.com/Miner/classes/Miner.html#getReplace)
*   [getReplaceString](http://jstayton.github.com/Miner/classes/Miner.html#getReplaceString)
*   [mergeReplaceInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeReplaceInto)

### UPDATE

*   [update](http://jstayton.github.com/Miner/classes/Miner.html#update)
*   [getUpdate](http://jstayton.github.com/Miner/classes/Miner.html#getUpdate)
*   [getUpdateString](http://jstayton.github.com/Miner/classes/Miner.html#getUpdateString)
*   [mergeUpdateInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeUpdateInto)

### DELETE

*   [delete](http://jstayton.github.com/Miner/classes/Miner.html#delete)
*   [getDeleteString](http://jstayton.github.com/Miner/classes/Miner.html#getDeleteString)
*   [mergeDeleteInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeDeleteInto)

### OPTIONS

*   [option](http://jstayton.github.com/Miner/classes/Miner.html#option)
*   [calcFoundRows](http://jstayton.github.com/Miner/classes/Miner.html#calcFoundRows)
*   [distinct](http://jstayton.github.com/Miner/classes/Miner.html#distinct)
*   [getOptionsString](http://jstayton.github.com/Miner/classes/Miner.html#getOptionsString)
*   [mergeOptionsInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeOptionsInto)

### SET

*   [set](http://jstayton.github.com/Miner/classes/Miner.html#set)
*   [getSetPlaceholderValues](http://jstayton.github.com/Miner/classes/Miner.html#getSetPlaceholderValues)
*   [getSetString](http://jstayton.github.com/Miner/classes/Miner.html#getSetString)
*   [mergeSetInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeSetInto)

### FROM

*   [from](http://jstayton.github.com/Miner/classes/Miner.html#from)
*   [innerJoin](http://jstayton.github.com/Miner/classes/Miner.html#innerJoin)
*   [leftJoin](http://jstayton.github.com/Miner/classes/Miner.html#leftJoin)
*   [rightJoin](http://jstayton.github.com/Miner/classes/Miner.html#rightJoin)
*   [join](http://jstayton.github.com/Miner/classes/Miner.html#join)
*   [getFrom](http://jstayton.github.com/Miner/classes/Miner.html#getFrom)
*   [getFromAlias](http://jstayton.github.com/Miner/classes/Miner.html#getFromAlias)
*   [getFromString](http://jstayton.github.com/Miner/classes/Miner.html#getFromString)
*   [getJoinString](http://jstayton.github.com/Miner/classes/Miner.html#getJoinString)
*   [mergeFromInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeFromInto)
*   [mergeJoinInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeJoinInto)

### WHERE

*   [where](http://jstayton.github.com/Miner/classes/Miner.html#where)
*   [andWhere](http://jstayton.github.com/Miner/classes/Miner.html#andWhere)
*   [orWhere](http://jstayton.github.com/Miner/classes/Miner.html#orWhere)
*   [whereIn](http://jstayton.github.com/Miner/classes/Miner.html#whereIn)
*   [whereNotIn](http://jstayton.github.com/Miner/classes/Miner.html#whereNotIn)
*   [whereBetween](http://jstayton.github.com/Miner/classes/Miner.html#whereBetween)
*   [whereNotBetween](http://jstayton.github.com/Miner/classes/Miner.html#whereNotBetween)
*   [openWhere](http://jstayton.github.com/Miner/classes/Miner.html#openWhere)
*   [closeWhere](http://jstayton.github.com/Miner/classes/Miner.html#closeWhere)
*   [getWherePlaceholderValues](http://jstayton.github.com/Miner/classes/Miner.html#getWherePlaceholderValues)
*   [getWhereString](http://jstayton.github.com/Miner/classes/Miner.html#getWhereString)
*   [mergeWhereInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeWhereInto)

### GROUP BY

*   [groupBy](http://jstayton.github.com/Miner/classes/Miner.html#groupBy)
*   [getGroupByString](http://jstayton.github.com/Miner/classes/Miner.html#getGroupByString)
*   [mergeGroupByInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeGroupByInto)

### HAVING

*   [having](http://jstayton.github.com/Miner/classes/Miner.html#having)
*   [andHaving](http://jstayton.github.com/Miner/classes/Miner.html#andHaving)
*   [orHaving](http://jstayton.github.com/Miner/classes/Miner.html#orHaving)
*   [havingIn](http://jstayton.github.com/Miner/classes/Miner.html#havingIn)
*   [havingNotIn](http://jstayton.github.com/Miner/classes/Miner.html#havingNotIn)
*   [havingBetween](http://jstayton.github.com/Miner/classes/Miner.html#havingBetween)
*   [havingNotBetween](http://jstayton.github.com/Miner/classes/Miner.html#havingNotBetween)
*   [openHaving](http://jstayton.github.com/Miner/classes/Miner.html#openHaving)
*   [closeHaving](http://jstayton.github.com/Miner/classes/Miner.html#closeHaving)
*   [getHavingPlaceholderValues](http://jstayton.github.com/Miner/classes/Miner.html#getHavingPlaceholderValues)
*   [getHavingString](http://jstayton.github.com/Miner/classes/Miner.html#getHavingString)
*   [mergeHavingInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeHavingInto)

### ORDER BY

*   [orderBy](http://jstayton.github.com/Miner/classes/Miner.html#orderBy)
*   [getOrderByString](http://jstayton.github.com/Miner/classes/Miner.html#getOrderByString)
*   [mergeOrderByInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeOrderByInto)

### LIMIT

*   [limit](http://jstayton.github.com/Miner/classes/Miner.html#limit)
*   [getLimit](http://jstayton.github.com/Miner/classes/Miner.html#getLimit)
*   [getLimitOffset](http://jstayton.github.com/Miner/classes/Miner.html#getLimitOffset)
*   [getLimitString](http://jstayton.github.com/Miner/classes/Miner.html#getLimitString)

### Statement

*   [execute](http://jstayton.github.com/Miner/classes/Miner.html#execute)
*   [getStatement](http://jstayton.github.com/Miner/classes/Miner.html#getStatement)
*   [getPlaceholderValues](http://jstayton.github.com/Miner/classes/Miner.html#getPlaceholderValues)
*   [isSelect](http://jstayton.github.com/Miner/classes/Miner.html#isSelect)
*   [isInsert](http://jstayton.github.com/Miner/classes/Miner.html#isInsert)
*   [isReplace](http://jstayton.github.com/Miner/classes/Miner.html#isReplace)
*   [isUpdate](http://jstayton.github.com/Miner/classes/Miner.html#isUpdate)
*   [isDelete](http://jstayton.github.com/Miner/classes/Miner.html#isDelete)
*   [__toString](http://jstayton.github.com/Miner/classes/Miner.html#__toString)
*   [mergeInto](http://jstayton.github.com/Miner/classes/Miner.html#mergeInto)

### Connection

*   [setPdoConnection](http://jstayton.github.com/Miner/classes/Miner.html#setPdoConnection)
*   [getPdoConnection](http://jstayton.github.com/Miner/classes/Miner.html#getPdoConnection)
*   [setAutoQuote](http://jstayton.github.com/Miner/classes/Miner.html#setAutoQuote)
*   [getAutoQuote](http://jstayton.github.com/Miner/classes/Miner.html#getAutoQuote)
*   [autoQuote](http://jstayton.github.com/Miner/classes/Miner.html#autoQuote)
*   [quote](http://jstayton.github.com/Miner/classes/Miner.html#quote)

Feedback
--------

Please open an issue to request a feature or submit a bug report. Or even if
you just want to provide some feedback, I'd love to hear. I'm also available on
Twitter as [@jstayton](http://twitter.com/jstayton).

Contributing
------------

1.  Fork it.
2.  Create your feature branch (`git checkout -b my-new-feature`).
3.  Commit your changes (`git commit -am 'Added some feature'`).
4.  Push to the branch (`git push origin my-new-feature`).
5.  Create a new Pull Request.
