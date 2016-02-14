Status
======
[![Build Status](https://travis-ci.org/baleen/migrations.svg?branch=master)](https://travis-ci.org/baleen/migrations)
[![Code Coverage](https://scrutinizer-ci.com/g/baleen/migrations/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/baleen/migrations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/6251e1ff-532d-4dad-a831-93dcf0561a49/mini.png)](https://insight.sensiolabs.com/projects/6251e1ff-532d-4dad-a831-93dcf0561a49)
[![Packagist](https://img.shields.io/packagist/v/baleen/migrations.svg)](https://packagist.org/packages/baleen/migrations)

[![Author](http://img.shields.io/badge/author-@gabriel_somoza-blue.svg)](https://twitter.com/gabriel_somoza)
[![Author](http://img.shields.io/badge/author-@__mikeSimonson-blue.svg)](https://twitter.com/_mikeSimonson)
[![License](https://img.shields.io/packagist/l/baleen/migrations.svg)](https://github.com/baleen/migrations/blob/master/LICENSE)
[![Documentation Status](https://readthedocs.org/projects/baleen/badge/?version=latest)](https://readthedocs.org/projects/baleen/?badge=latest)

**NB!:** This project is still an early release. Please do not use in 
production-critical environments. Refer to the [LICENSE](https://github.com/baleen/migrations/blob/master/LICENSE)
for more information.

Introduction
============
Baleen Migrations is a project that seeks to abstract the domain logic of performing migrations of any kind into a 
single package. Its simple goal is to excel at one single task: provide an intuitive, well-tested framework to migrate 
from point A to point B (or vice-versa if going down).

In other words, we take care of WHICH migrations are run and in what ORDER. Everything else is left up to the 
implementation:

* WHAT is going to be migrated? It could be a database, images, documents, etc.
* HOW its going to be migrated? You can wrap each migration into DB transactions. Or not, its up to you.
* What to do when a migration fails? We'll let you know WHEN it happens, but its up to you to the implementation to
decide what to do (e.g. cancel the transaction).

Baleen CLI: Our Command-Line Tool
---------------------------------
Are you looking for a framework-agnostic migration tool that can be used right out of the box? Then you're almost at the right place: go visit [baleen/cli](https://github.com/baleen/cli) and get started immediately after requiring it into your project.

Once you're there you'll see that its much more than just a migrations tool: its also a migrations *framework* that you can use to build a customized migration experience for your projects and their unique use-cases.

If you're interested in creating another tool around the core domain then you're invited to read on.

Installation (Composer)
=======================
Installation with Composer is simple:  

    composer require baleen/migrations

Documentation
=============
For more documentation please refer to the [online documentation](http://baleen.readthedocs.org/en/latest/).

Contributing
============
See [CONTRIBUTING.md](https://github.com/baleen/migrations/blob/master/CONTRIBUTING.md)

Roadmap
============
* We're still working on making some changes to this core domain. See branch `ddd-changes` for the latest progress. Code reviews / constructive critiques are more than welcome, and so are of course PRs!
* Once we're happy with the core API we'll release `v1.0`.
* With a stable core we'll shift our focus to `baleen/cli` and its future specializations (i.e. Doctrine Migrations, etc).

LICENSE
=======
MIT - for more details please refer to [LICENSE](https://github.com/baleen/migrations/blob/master/LICENSE) at the root 
directory.

### About the name
We named the project ("BALEEN") after a family (or "parvorders" to be precise) of whales that are famous for migrating 
long distances. The humpback whale, for example, travels as far as 16,000 miles (25749.5 km) annually. That's about 
twice the earth's diameter.
