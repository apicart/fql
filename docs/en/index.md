# Introduction

Write filter query as simple string via Filter Query Language (FQL) syntax. Filter query will be parsed into easy-to-use syntax tree.

**Simple FQL example:**

`q:"samsung" AND introducedAt:["2018-01-01 00:00:00" TO NOW] AND (type:tv OR type:mobile)`


## Syntax

FQL is based on a syntax that seems to be the unofficial standard for search query as user input. It should feel familiar, as the same basic syntax is used by any popular text-based search engine out there. It is also very similar to Lucene Query Parser syntax, used by both Solr and Elasticsearch.

