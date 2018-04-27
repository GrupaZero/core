# GZERO Core [![Build Status](https://travis-ci.org/GrupaZero/core.svg?branch=master)](https://travis-ci.org/GrupaZero/core) [![Coverage Status](https://coveralls.io/repos/GrupaZero/core/badge.png)](https://coveralls.io/r/GrupaZero/core)

It's a core package for GZERO platform

###Testing

To run tests, copy .env.example file to .env.testing and put your database credentials into it.

To run tests you can use one of those commands:

#####whole suit

`composer test`

#####single file

`composer test tests/unit/jobs/UserJobsTest`

#####single test

`composer test tests/unit/jobs/UserJobsTest:canDeleteUser`

###Quality
```
./vendor/bin/phpmd src/ text phpmd.xml
./vendor/bin/phpcs --standard=ruleset.xml src/ -n --colors
```
