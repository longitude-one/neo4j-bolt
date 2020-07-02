<?php

namespace GraphAware\Bolt\Tests;

use GraphAware\Bolt\GraphDatabase;
use PHPUnit\Framework\TestCase;

class IntegrationTestCase extends TestCase
{
    /**
     * @var \GraphAware\Bolt\Driver
     */
    protected $driver;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->driver = GraphDatabase::driver("bolt://localhost");
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

    /**
     * Empty the database
     */
    protected function emptyDB()
    {
        $this->getSession()->run('MATCH (n) DETACH DELETE n');
    }
}
