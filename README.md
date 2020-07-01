## Neo4j Bolt PHP

PHP low level Driver for Neo4j's Bolt Remoting Protocol
Neo4j Bolt PHP is a [repository](https://github.com/graphaware/neo4j-bolt-php) initially created by [GraphAware](https://www.graphaware.com).
As it is a MIT library not upgraded since 2017, LongitudeOne decided to fork it and upgrade it.

[![Version](https://poser.pugx.org/longitude-one/neo4j-bolt/version)](//packagist.org/packages/longitude-one/neo4j-bolt)
[![Build Status](https://travis-ci.org/longitude-one/neo4j-bolt.svg?branch=master)](https://travis-ci.org/longitude-one/neo4j-bolt)
[![License](https://poser.pugx.org/longitude-one/neo4j-bolt/license)](//packagist.org/packages/longitude-one/neo4j-bolt)
[![Total Downloads](https://poser.pugx.org/longitude-one/neo4j-bolt/downloads)](//packagist.org/packages/longitude-one/neo4j-bolt)
---

### References :

* PHP Client embedding Bolt along with the http driver (recommended way of using Neo4j in PHP) : https://github.com/longitude-one/neo4j-php-client
* Neo4j 3.5 : http://neo4j.com/docs

### Requirements:

* PHP7.3+
* Neo4j3.0 (3.5 recommended)
* PHP Sockets extension available
* `bcmath` extension
* `json` extension
* `mbstring` extension

This driver isn't compatible with Neo4j 4.0 nor 4.1.

### Installation

Require the package in your dependencies :

```bash
composer require longitude-one/neo4j-bolt
```

### Setting up a driver and creating a session

```php

use GraphAware\Bolt\GraphDatabase;

$driver = GraphDatabase::driver("bolt://localhost");
$session = $driver->session();
```

### Sending a Cypher statement

```php
$session = $driver->session();
$session->run("CREATE (n)");
$session->close();

// with parameters :

$session->run("CREATE (n) SET n += {props}", ['name' => 'Mike', 'age' => 27]);
```

### Empty Arrays

Due to lack of Collections types in php, there is no way to distinguish when an empty array
should be treated as equivalent Java List or Map types.

Therefore you can use a wrapper around arrays for type safety :

```php
use GraphAware\Common\Collections;

        $query = 'MERGE (n:User {id: {id} }) 
        WITH n
        UNWIND {friends} AS friend
        MERGE (f:User {id: friend.name})
        MERGE (f)-[:KNOWS]->(n)';

        $params = ['id' => 'me', 'friends' => Collections::asList([])];
        $this->getSession()->run($query, $params);
        
// Or

        $query = 'MERGE (n:User {id: {id} }) 
        WITH n
        UNWIND {friends}.users AS friend
        MERGE (f:User {id: friend.name})
        MERGE (f)-[:KNOWS]->(n)';

        $params = ['id' => 'me', 'friends' => Collections::asMap([])];
        $this->getSession()->run($query, $params);

```

### TLS Encryption

In order to enable TLS support, you need to set the configuration option to `REQUIRED`, here an example :

```php
$config = \GraphAware\Bolt\Configuration::newInstance()
    ->withCredentials('bolttest', 'L7n7SfTSj0e6U')
    ->withTLSMode(\GraphAware\Bolt\Configuration::TLSMODE_REQUIRED);

$driver = \GraphAware\Bolt\GraphDatabase::driver('bolt://hobomjfhocgbkeenl.dbs.graphenedb.com:24786', $config);
$session = $driver->session();
```

### License [![License](https://poser.pugx.org/longitude-one/neo4j-bolt/license)](//packagist.org/packages/longitude-one/neo4j-bolt)

Copyright (c) 2020      LongitudeOne
Copyright (c) 2015-2016 GraphAware Ltd

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

---