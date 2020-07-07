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

namespace GraphAware\Bolt\Protocol\Constants;

class Signature
{
    //FIXME remove SIGNATURE_
    public const SIGNATURE_ACK_FAILURE = 0x0f;
    public const SIGNATURE_BEGIN = 0x11;
    public const SIGNATURE_DISCARD_ALL = 0x2f;
    public const SIGNATURE_FAILURE = 0x7f;
    public const SIGNATURE_GOODBYE = 0x02;
    public const SIGNATURE_IGNORE = 0x7E;
    public const SIGNATURE_INIT = 0x01;
    public const SIGNATURE_NODE = 0x4e;
    public const SIGNATURE_PATH = 0x50;
    public const SIGNATURE_PULL_ALL = 0x3f;
    public const SIGNATURE_RECORD = 0x71;
    public const SIGNATURE_RELATIONSHIP = 0x52;
    public const SIGNATURE_RESET = 0x0F;
    public const SIGNATURE_RUN = 0x10;
    public const SIGNATURE_SUCCESS = 0x70;
    public const SIGNATURE_UNBOUND_RELATIONSHIP = 0x72;

    public const SIGNATURES = [
        self::SIGNATURE_ACK_FAILURE => 'ACK_FAILURE',
        self::SIGNATURE_BEGIN => 'BEGIN',
        self::SIGNATURE_DISCARD_ALL => 'DISCARD_ALL',
        self::SIGNATURE_FAILURE => 'FAILURE',
        self::SIGNATURE_GOODBYE => 'GOODBYE',
        self::SIGNATURE_IGNORE => 'IGNORED',
        self::SIGNATURE_INIT => 'INIT',
        self::SIGNATURE_NODE => 'NODE',
        self::SIGNATURE_PATH => 'PATH',
        self::SIGNATURE_PULL_ALL => 'PULL_ALL',
        self::SIGNATURE_RECORD => 'RECORD',
        self::SIGNATURE_RESET => 'RESET',
        self::SIGNATURE_RELATIONSHIP => 'RELATIONSHIP',
        self::SIGNATURE_RUN => 'RUN',
        self::SIGNATURE_SUCCESS => 'SUCCESS',
        self::SIGNATURE_UNBOUND_RELATIONSHIP => 'UNBOUND_RELATIONSHIP',
    ];
}
