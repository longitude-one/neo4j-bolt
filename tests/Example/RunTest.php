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

namespace GraphAware\Bolt\Tests\Example;

use GraphAware\Bolt\Protocol\SessionInterface;
use GraphAware\Bolt\Tests\IntegrationTestCase;

/**
 * @group example
 * @group movies
 *
 * @internal
 * @coversNothing
 */
class RunTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testRun(): void
    {
        $session = $this->getSession();
        $this->assertInstanceOf(SessionInterface::class, $session);
        $session->run('MATCH (n) DETACH DELETE n');
        $session->close();
    }
}
