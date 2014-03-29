Miner
=====

[![Latest Stable Version](https://poser.pugx.org/jstayton/miner/v/stable.png)](https://packagist.org/packages/jstayton/miner)
[![Total Downloads](https://poser.pugx.org/jstayton/miner/downloads.png)](https://packagist.org/packages/jstayton/miner)

A dead simple PHP class for building SQL statements. No manual string
concatenation necessary.

Developed by [Justin Stayton](http://twitter.com/jstayton) while at
[Monk Development](http://monkdev.com).

*   [Documentation](http://jstayton.github.io/Miner/classes/Miner.html)
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

```json
{
    "require": {
        "jstayton/miner": "*"
    }
}
```

[More details](http://packagist.org/packages/jstayton/miner) can be found over
at [Packagist](http://packagist.org).

### Manually

1.  Copy `src/Miner.php` to your codebase, perhaps to the `vendor` directory.
2.  Add the `Miner` class to your autoloader or `require` the file directly.

Getting Started
---------------

Composing SQL with Miner is very similar to writing it by hand, as much of the
syntax maps directly to methods:

```php
$Miner = new Miner();
$Miner->select('*')
      ->from('shows')
      ->innerJoin('episodes', 'show_id')
      ->where('shows.network_id', 12)
      ->orderBy('episodes.aired_on', Miner::ORDER_BY_DESC)
      ->limit(20);
```

Now that the statement is built,

```php
$Miner->getStatement();
```

returns the full SQL string with placeholders (?), and

```php
$Miner->getPlaceholderValues();
```

returns the array of placeholder values that can then be passed to your
database connection or abstraction layer of choice. Or, if you'd prefer it all
at once, you can get the SQL string with values already safely quoted:

```php
$Miner->getStatement(false);
```

If you're using PDO, however, Miner makes executing the statement even easier:

```php
$PDOStatement = $Miner->execute();
```

Miner works directly with your PDO connection, which can be passed during
creation of the Miner object

```php
$Miner = new Miner($PDO);
```

or after

```php
$Miner->setPdoConnection($PDO);
```

Usage
-----

### SELECT

```mysql
SELECT *
FROM shows
INNER JOIN episodes
  ON shows.show_id = episodes.show_id
WHERE shows.network_id = 12
ORDER BY episodes.aired_on DESC
LIMIT 20
```

With Miner:

```php
$Miner->select('*')
      ->from('shows')
      ->innerJoin('episodes', 'show_id')
      ->where('shows.network_id', 12)
      ->orderBy('episodes.aired_on', Miner::ORDER_BY_DESC)
      ->limit(20);
```

### INSERT

```mysql
INSERT HIGH_PRIORITY shows
SET network_id = 13,
    name = 'Freaks & Geeks',
    air_day = 'Tuesday'
```

With Miner:

```php
$Miner->insert('shows')
      ->option('HIGH_PRIORITY')
      ->set('network_id', 13)
      ->set('name', 'Freaks & Geeks')
      ->set('air_day', 'Tuesday');
```

### REPLACE

```mysql
REPLACE shows
SET network_id = 13,
    name = 'Freaks & Geeks',
    air_day = 'Monday'
```

With Miner:

```php
$Miner->replace('shows')
      ->set('network_id', 13)
      ->set('name', 'Freaks & Geeks')
      ->set('air_day', 'Monday');
```

### UPDATE

```mysql
UPDATE episodes
SET aired_on = '2012-06-25'
WHERE show_id = 12
  OR (name = 'Girlfriends and Boyfriends'
        AND air_day != 'Monday')
```

With Miner:

```php
$Miner->update('episodes')
      ->set('aired_on', '2012-06-25')
      ->where('show_id', 12)
      ->openWhere(Miner::LOGICAL_OR)
      ->where('name', 'Girlfriends and Boyfriends')
      ->where('air_day', 'Monday', Miner::NOT_EQUALS)
      ->closeWhere();
```

### DELETE

```mysql
DELETE
FROM shows
WHERE show_id IN (12, 15, 20)
LIMIT 3
```

With Miner:

```php
$Miner->delete()
      ->from('shows')
      ->whereIn('show_id', array(12, 15, 20))
      ->limit(3);
```

Methods
-------

*   [__construct](http://jstayton.github.io/Miner/classes/Miner.html#method___construct)

### SELECT

*   [select](http://jstayton.github.io/Miner/classes/Miner.html#method_select)
*   [getSelectString](http://jstayton.github.io/Miner/classes/Miner.html#method_getSelectString)
*   [mergeSelectInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeSelectInto)

### INSERT

*   [insert](http://jstayton.github.io/Miner/classes/Miner.html#method_insert)
*   [getInsert](http://jstayton.github.io/Miner/classes/Miner.html#method_getInsert)
*   [getInsertString](http://jstayton.github.io/Miner/classes/Miner.html#method_getInsertString)
*   [mergeInsertInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeInsertInto)

### REPLACE

*   [replace](http://jstayton.github.io/Miner/classes/Miner.html#method_replace)
*   [getReplace](http://jstayton.github.io/Miner/classes/Miner.html#method_getReplace)
*   [getReplaceString](http://jstayton.github.io/Miner/classes/Miner.html#method_getReplaceString)
*   [mergeReplaceInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeReplaceInto)

### UPDATE

*   [update](http://jstayton.github.io/Miner/classes/Miner.html#method_update)
*   [getUpdate](http://jstayton.github.io/Miner/classes/Miner.html#method_getUpdate)
*   [getUpdateString](http://jstayton.github.io/Miner/classes/Miner.html#method_getUpdateString)
*   [mergeUpdateInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeUpdateInto)

### DELETE

*   [delete](http://jstayton.github.io/Miner/classes/Miner.html#method_delete)
*   [getDeleteString](http://jstayton.github.io/Miner/classes/Miner.html#method_getDeleteString)
*   [mergeDeleteInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeDeleteInto)

### OPTIONS

*   [option](http://jstayton.github.io/Miner/classes/Miner.html#method_option)
*   [calcFoundRows](http://jstayton.github.io/Miner/classes/Miner.html#method_calcFoundRows)
*   [distinct](http://jstayton.github.io/Miner/classes/Miner.html#method_distinct)
*   [getOptionsString](http://jstayton.github.io/Miner/classes/Miner.html#method_getOptionsString)
*   [mergeOptionsInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeOptionsInto)

### SET / VALUES

*   [set](http://jstayton.github.io/Miner/classes/Miner.html#method_set)
*   [values](http://jstayton.github.io/Miner/classes/Miner.html#method_values)
*   [getSetPlaceholderValues](http://jstayton.github.io/Miner/classes/Miner.html#method_getSetPlaceholderValues)
*   [getSetString](http://jstayton.github.io/Miner/classes/Miner.html#method_getSetString)
*   [mergeSetInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeSetInto)

### FROM

*   [from](http://jstayton.github.io/Miner/classes/Miner.html#method_from)
*   [innerJoin](http://jstayton.github.io/Miner/classes/Miner.html#method_innerJoin)
*   [leftJoin](http://jstayton.github.io/Miner/classes/Miner.html#method_leftJoin)
*   [rightJoin](http://jstayton.github.io/Miner/classes/Miner.html#method_rightJoin)
*   [join](http://jstayton.github.io/Miner/classes/Miner.html#method_join)
*   [getFrom](http://jstayton.github.io/Miner/classes/Miner.html#method_getFrom)
*   [getFromAlias](http://jstayton.github.io/Miner/classes/Miner.html#method_getFromAlias)
*   [getFromString](http://jstayton.github.io/Miner/classes/Miner.html#method_getFromString)
*   [getJoinString](http://jstayton.github.io/Miner/classes/Miner.html#method_getJoinString)
*   [mergeFromInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeFromInto)
*   [mergeJoinInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeJoinInto)

### WHERE

*   [where](http://jstayton.github.io/Miner/classes/Miner.html#method_where)
*   [andWhere](http://jstayton.github.io/Miner/classes/Miner.html#method_andWhere)
*   [orWhere](http://jstayton.github.io/Miner/classes/Miner.html#method_orWhere)
*   [whereIn](http://jstayton.github.io/Miner/classes/Miner.html#method_whereIn)
*   [whereNotIn](http://jstayton.github.io/Miner/classes/Miner.html#method_whereNotIn)
*   [whereBetween](http://jstayton.github.io/Miner/classes/Miner.html#method_whereBetween)
*   [whereNotBetween](http://jstayton.github.io/Miner/classes/Miner.html#method_whereNotBetween)
*   [openWhere](http://jstayton.github.io/Miner/classes/Miner.html#method_openWhere)
*   [closeWhere](http://jstayton.github.io/Miner/classes/Miner.html#method_closeWhere)
*   [getWherePlaceholderValues](http://jstayton.github.io/Miner/classes/Miner.html#method_getWherePlaceholderValues)
*   [getWhereString](http://jstayton.github.io/Miner/classes/Miner.html#method_getWhereString)
*   [mergeWhereInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeWhereInto)

### GROUP BY

*   [groupBy](http://jstayton.github.io/Miner/classes/Miner.html#method_groupBy)
*   [getGroupByString](http://jstayton.github.io/Miner/classes/Miner.html#method_getGroupByString)
*   [mergeGroupByInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeGroupByInto)

### HAVING

*   [having](http://jstayton.github.io/Miner/classes/Miner.html#method_having)
*   [andHaving](http://jstayton.github.io/Miner/classes/Miner.html#method_andHaving)
*   [orHaving](http://jstayton.github.io/Miner/classes/Miner.html#method_orHaving)
*   [havingIn](http://jstayton.github.io/Miner/classes/Miner.html#method_havingIn)
*   [havingNotIn](http://jstayton.github.io/Miner/classes/Miner.html#method_havingNotIn)
*   [havingBetween](http://jstayton.github.io/Miner/classes/Miner.html#method_havingBetween)
*   [havingNotBetween](http://jstayton.github.io/Miner/classes/Miner.html#method_havingNotBetween)
*   [openHaving](http://jstayton.github.io/Miner/classes/Miner.html#method_openHaving)
*   [closeHaving](http://jstayton.github.io/Miner/classes/Miner.html#method_closeHaving)
*   [getHavingPlaceholderValues](http://jstayton.github.io/Miner/classes/Miner.html#method_getHavingPlaceholderValues)
*   [getHavingString](http://jstayton.github.io/Miner/classes/Miner.html#method_getHavingString)
*   [mergeHavingInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeHavingInto)

### ORDER BY

*   [orderBy](http://jstayton.github.io/Miner/classes/Miner.html#method_orderBy)
*   [getOrderByString](http://jstayton.github.io/Miner/classes/Miner.html#method_getOrderByString)
*   [mergeOrderByInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeOrderByInto)

### LIMIT

*   [limit](http://jstayton.github.io/Miner/classes/Miner.html#method_limit)
*   [getLimit](http://jstayton.github.io/Miner/classes/Miner.html#method_getLimit)
*   [getLimitOffset](http://jstayton.github.io/Miner/classes/Miner.html#method_getLimitOffset)
*   [getLimitString](http://jstayton.github.io/Miner/classes/Miner.html#method_getLimitString)

### Statement

*   [execute](http://jstayton.github.io/Miner/classes/Miner.html#method_execute)
*   [getStatement](http://jstayton.github.io/Miner/classes/Miner.html#method_getStatement)
*   [getPlaceholderValues](http://jstayton.github.io/Miner/classes/Miner.html#method_getPlaceholderValues)
*   [isSelect](http://jstayton.github.io/Miner/classes/Miner.html#method_isSelect)
*   [isInsert](http://jstayton.github.io/Miner/classes/Miner.html#method_isInsert)
*   [isReplace](http://jstayton.github.io/Miner/classes/Miner.html#method_isReplace)
*   [isUpdate](http://jstayton.github.io/Miner/classes/Miner.html#method_isUpdate)
*   [isDelete](http://jstayton.github.io/Miner/classes/Miner.html#method_isDelete)
*   [__toString](http://jstayton.github.io/Miner/classes/Miner.html#method___toString)
*   [mergeInto](http://jstayton.github.io/Miner/classes/Miner.html#method_mergeInto)

### Connection

*   [setPdoConnection](http://jstayton.github.io/Miner/classes/Miner.html#method_setPdoConnection)
*   [getPdoConnection](http://jstayton.github.io/Miner/classes/Miner.html#method_getPdoConnection)
*   [setAutoQuote](http://jstayton.github.io/Miner/classes/Miner.html#method_setAutoQuote)
*   [getAutoQuote](http://jstayton.github.io/Miner/classes/Miner.html#method_getAutoQuote)
*   [autoQuote](http://jstayton.github.io/Miner/classes/Miner.html#method_autoQuote)
*   [quote](http://jstayton.github.io/Miner/classes/Miner.html#method_quote)

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
