Status
======
[![Build Status](https://travis-ci.org/baleen/migrations.svg?branch=master)](https://travis-ci.org/baleen/migrations)
[![Code Coverage](https://scrutinizer-ci.com/g/baleen/migrations/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/baleen/migrations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/6251e1ff-532d-4dad-a831-93dcf0561a49.svg)](https://insight.sensiolabs.com/projects/6251e1ff-532d-4dad-a831-93dcf0561a49)
[![Packagist](https://img.shields.io/packagist/v/symfony/symfony.svg)](https://packagist.org/packages/baleen/migrations)

[![Author](http://img.shields.io/badge/author-@gabriel_somoza-blue.svg)](https://twitter.com/gabriel_somoza)
[![Author](http://img.shields.io/badge/author-@_mikeSimonson-blue.svg)](https://twitter.com/_mikeSimonson)
[![License](https://img.shields.io/packagist/l/baleen/migrations.svg)](https://github.com/baleen/migrations/blob/master/LICENSE)
[![Documentation Status](https://readthedocs.org/projects/baleen/badge/?version=latest)](https://readthedocs.org/projects/baleen/?badge=latest)

**NB!:** This project is still an early release. Please do not use in 
production-critical environments. Refer to the the [LICENSE](https://github.com/baleen/migrations/blob/master/LICENSE)
for more information.

Introduction
======
Baleen Migrations is a project that seeks to abstract the domain logic of performing migrations of any kind into a 
single package. Its simple goal is to excel at one single task: provide an intuitive, well-tested framework to migrate 
from point A to point B (or vice-versa if going down).

In other words, we take care of WHICH migrations are run and in what ORDER. Everything else is left up to the 
implementation:

* WHAT is going to be migrated? It could be a database, images, documents, etc.
* HOW its going to be migrated? You can wrap each migration into DB transactions. Or not, its up to you.
* What to do when a migration fails? We'll let you know WHEN it happens, but its up to you to the implementation to
decide what to do (e.g. cancel the transaction).

Installation (Composer)
=======================
Installation with Composer is simple:  

    composer install baleen/migrations:^0.1

Documentation
=============
For more documentation please refer to the [online documentation](http://baleen.readthedocs.org/en/latest/) (or build
the `./docs` folder locally).

Contributing
============
See CONTRIBUTING.md

LICENSE
=======
MIT - for more details please refer to [LICENSE](https://github.com/baleen/migrations/blob/master/LICENSE) at the root 
directory.

### About the name
We named the project ("BALEEN") after a family (or "parvorders" to be precise) of whales that are famous for migrating 
long distances. The humpback whale, for example, travels as far as 16,000 miles (25749.5 km) annually. That's about 
twice the earth's diameter.
