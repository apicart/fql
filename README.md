# Apicart/FQL (Filter Query Language)

[![Build Status](https://img.shields.io/travis/apicart/fql.svg?style=flat-square)](https://travis-ci.org/apicart/fql)
[![Quality Score](https://img.shields.io/scrutinizer/g/apicart/fql.svg?style=flat-square)](https://scrutinizer-ci.com/g/apicart/fql)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/apicart/fql.svg?style=flat-square)](https://scrutinizer-ci.com/g/apicart/fql)
[![Downloads](https://img.shields.io/packagist/dt/apicart/fql.svg?style=flat-square)](https://packagist.org/packages/apicart/fql)
[![Latest stable](https://img.shields.io/github/tag/apicart/fql.svg?style=flat-square)](https://packagist.org/packages/apicart/fql)

Write filter query as simple string via Filter Query Language (FQL) syntax. Filter query will be parsed into easy-to-use syntax tree.

**Some FQL query example:**

`q:"samsung" AND introducedAt:["2018-01-01 00:00:00" TO NOW] AND (type:tv OR type:mobile)`


## Installation

The simplest way to install Apicart/FQL is using  [Composer](http://getcomposer.org/):

```sh
$ composer require apicart/fql
```


## Resources

 * [Documentation](https://github.com/apicart/fql/blob/master/docs/en/index.md)
 * [Contributing](https://github.com/apicart/fql/blob/master/CODE_OF_CONDUCT.md)
 * [Report issues](https://github.com/apicart/fql/issues) and [send Pull Requests](https://github.com/apicart/fql/pulls).
