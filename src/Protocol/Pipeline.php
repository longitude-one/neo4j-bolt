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

use GraphAware\Bolt\Exception\BoltInvalidArgumentException;
use GraphAware\Bolt\Protocol\Message\PullAllMessage;
use GraphAware\Bolt\Protocol\Message\RunMessage;
use GraphAware\Common\Driver\PipelineInterface;
use GraphAware\Common\Result\ResultCollection;

class Pipeline implements PipelineInterface
{
    /**
     * @var RunMessage[]
     */
    protected $messages = [];

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @return RunMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->messages);
    }

    /**
     * {@inheritdoc}
     */
    public function push($query, array $parameters = [], $tag = null)
    {
        if (null === $query) {
            throw new BoltInvalidArgumentException('Statement cannot be null');
        }
        $this->messages[] = new RunMessage($query, $parameters, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        //$pullAllMessage = new PullAllMessage();
        $resultCollection = new ResultCollection();

        foreach ($this->messages as $message) {
            $result = $this->session->run($message->getStatement(), $message->getParams(), $message->getTag());
            $resultCollection->add($result);
        }

        return $resultCollection;
    }
}
