<?php
/**
 * This file is part of the LongitudeOne Neo4j Bolt driver for PHP.
 *
 * PHP version 7.2|7.3|7.4
 * Neo4j 3.0|3.5|4.0|4.1
 *
 * (c) Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * (c) Longitude One 2020
 * (c) Graph Aware Limited <http://graphaware.com> 2015-2016
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GraphAware\Bolt\Tests;

use GraphAware\Bolt\GraphDatabase;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class IntegrationTestCase extends TestCase
{
    /**
     * @var \GraphAware\Bolt\Driver
     */
    protected $driver;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->driver = GraphDatabase::driver('bolt://localhost');
    }

    /**
     * Empty the database.
     */
    protected function emptyDB()
    {
        return $this->getSession()->run('MATCH (n) DETACH DELETE n');
    }

    /**
     * @return \GraphAware\Bolt\Driver
     */
    protected function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return \Graphaware\Bolt\Protocol\SessionInterface
     */
    protected function getSession()
    {
        return $this->driver->session();
    }
}
