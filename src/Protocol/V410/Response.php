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

namespace GraphAware\Bolt\Protocol\V410;

use GraphAware\Bolt\Exception\MessageFailureException;

class Response
{
    /**
     * @var bool
     */
    protected $completed = false;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $records = [];

    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->completed;
    }

    /**
     * @param $metadata
     *
     * @throws MessageFailureException
     */
    public function onFailure($metadata)
    {
        $this->completed = true;
        $e = new MessageFailureException($metadata->getElements()['message']);
        $e->setStatusCode($metadata->getElements()['code']);

        throw $e;
    }

    /**
     * @param $metadata
     */
    public function onRecord($metadata)
    {
        $this->records[] = $metadata;
    }

    /**
     * @param $metadata
     */
    public function onSuccess($metadata)
    {
        $this->completed = true;
        $this->metadata[] = $metadata;
    }
}
