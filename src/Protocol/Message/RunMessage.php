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

namespace GraphAware\Bolt\Protocol\Message;

use GraphAware\Bolt\Protocol\Constants\Signature;

class RunMessage extends AbstractMessage
{
    const MESSAGE_TYPE = 'RUN';

    /**
     * @var array
     */
    protected $params;

    /**
     * @var string
     */
    protected $statement;

    /**
     * @var string|null
     */
    protected $tag;

    /**
     * @param string      $statement
     * @param string|null $tag
     */
    public function __construct($statement, array $params = [], $tag = null)
    {
        parent::__construct(Signature::SIGNATURE_RUN);
        $this->fields = [$statement, $params];
        $this->statement = $statement;
        $this->params = $params;
        $this->tag = $tag;
    }

    /**
     * {@inheritdoc}
     */
    public function getFields()
    {
        return [$this->statement, $this->params];
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType()
    {
        return self::MESSAGE_TYPE;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * @return string|null
     */
    public function getTag()
    {
        return $this->tag;
    }
}
