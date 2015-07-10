Status
======
[![Build Status](https://scrutinizer-ci.com/g/baleen/migrations/badges/build.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/build-status/master)

[![Code Coverage](https://scrutinizer-ci.com/g/baleen/migrations/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/baleen/migrations/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/baleen/migrations/?branch=master)


**NB!:** This project is still an early release. Please do not use in 
production-critical environments. We do not guarantee the safety of your data - use
at your own risk.

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

Example
=======
Given the following migrations available in a system:

```php
use Baleen\Migration\MigrationInterface;
use Baleen\Migration\RunOptions;

/**
 * FILE: ./migrations/AbstractPDOMigration.php 
 * 
 * You can be as creative as you want here. The only requirement here is to implement 
 * MigrationInterface.
 */
class AbstractPDOMigration implements MigrationInterface
{
    protected $connection; // gets set on the constructor
    
    /** @var RunOptions **/
    protected $options;
    
    public function begin() {
        $this->connection->beginTransaction();
    }
    
    public function finish() {
        $this->connection->commit();
    }
    
    public function abort() {
        $this->connection->rollBack();
    }
    
    public function setOptions(RunOptions $options) {
        $this->options = $options;
    }
}

// FILE: ./migrations/v001_AddHireDateToStaff.php
class v001_AddHireDateToStaff extends AbstractPDOMigration
{
    public function up() {
        $this->connection->exec("ALTER TABLE staff ADD hire_date date");
    }
    
    public function down() {
        $this->connection->exec("ALTER TABLE staff ADD hire_date date");
    }
}

// FILE: ./migrations/v002_SeedJoeBloggs.php
class v002_SeedJoeBloggs extends AbstractPDOMigration
{
    public function up() {
        $this->connection->exec(
            "INSERT INTO staff (id, first, last) VALUES (23, 'Joe', 'Bloggs')"
        );
    }
    
    public function down() {
        $this->connection->exec("DELETE FROM staff WHERE id = 23");
    }   
}

// ... etc - for the purposes of this example imagine there are 100 migrations
```

Here's what Baleen Migrations can do:

```php
#!/bin/env/php
// FILE: run_migrations.php 

require __DIR__ . '/vendor/autoload.php';

// The repository is in charge of loading all available migrations.
$repository = new DirectoryRepository(__DIR__ . '/migrations');
$availableMigrations = $repository->getAllAvailableMigrations();

/* The storage is in charge of knowing which versions have already been run.
   Here we're loading from a file, but it could also be a DB table, API call, etc. */
$storage = new FileStorage(__DIR__ . '/versions.txt');
$alreadyRun = $storage->readMigratedVersions();

/* The timeline is in charge of ordering migrations and running them based on their 
   status */
$timeline = new Timeline($availableMigrations, $alreadyRun);

// Say we want to make sure all migrations up to and including v015 are UP:
$timeline->upTowards('v015');

// Now lets revert all migrations down to 13 (inclusive) 
$timeline->downTowards('v013'); // will revert 15, 14 and 13 - in that order

/* You can also run a single migration in any direction and pass custom arguments
   to the Migration. */
use Baleen\Migration\RunOptions;
$options = new RunOptions(RunOptions::UP);
$options->setCustom([
    'notifyEmail' => 'jon@doe.me',
]);
$timeline->runSingle('v100', $options);
/* Version 'v100' will receive an instance of RunOptions through the setOptions 
   method. You can also pass RunOptions to most of the other Timeline methods. */
   
```

Documentation
=============
For more documentation please refer to the docs directory (TODO).

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
