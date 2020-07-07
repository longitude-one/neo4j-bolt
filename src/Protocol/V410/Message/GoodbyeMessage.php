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

namespace GraphAware\Bolt\Protocol\V410\Message;

use GraphAware\Bolt\Protocol\Constants\Signature;
use GraphAware\Bolt\Protocol\Message\AbstractMessage;
use GraphAware\Bolt\Protocol\Message\MessageInterface;

class GoodbyeMessage extends AbstractMessage implements MessageInterface
{
    const MESSAGE_TYPE = 'GOODBYE';

    public function __construct()
    {
        parent::__construct(Signature::SIGNATURE_GOODBYE);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType()
    {
        return self::MESSAGE_TYPE;
    }
}
