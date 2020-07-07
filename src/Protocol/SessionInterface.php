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

namespace GraphAware\Bolt\Protocol;

use GraphAware\Common\Driver\SessionInterface as BaseSessionInterface;

interface SessionInterface extends BaseSessionInterface
{
    /**
     * @return string
     */
    public static function getProtocolVersion();

    /**
     * @param string|null $query
     * @param string|null $tag
     *
     * @return Pipeline
     */
    public function createPipeline($query = null, array $parameters = [], $tag = null);

    /**
     * @param string      $statement
     * @param string|null $tag
     *
     * @return \GraphAware\Bolt\Result\Result
     */
    public function run($statement, array $parameters = [], $tag = null);

    /**
     * @return mixed
     */
    public function runPipeline(Pipeline $pipeline);

    /**
     * @return \GraphAware\Bolt\Protocol\V100\Transaction
     */
    public function transaction();
}
