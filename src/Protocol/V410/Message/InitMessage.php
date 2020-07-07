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

use GraphAware\Bolt\Protocol\Constants;
use GraphAware\Bolt\Protocol\Message\AbstractMessage;
use GraphAware\Bolt\Protocol\Message\MessageInterface;

class InitMessage extends AbstractMessage implements MessageInterface
{
    const MESSAGE_TYPE = 'INIT';

    /**
     * @param string $userAgent
     */
    public function __construct($userAgent, array $credentials)
    {
        $fields = [
            'user_agent' => $userAgent,
            'scheme' => 'none',
        ];

        if (isset($credentials[1]) && null !== $credentials[1]) {
            $fields['scheme'] = 'basic';
            $fields['principal'] = $credentials[0];
            $fields['credentials'] = $credentials[1];
        }

        parent::__construct(Constants::SIGNATURE_INIT, [$fields]); //Do not remove this fields!
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageType()
    {
        return self::MESSAGE_TYPE;
    }
}
