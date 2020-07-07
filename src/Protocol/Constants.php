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

class Constants
{
    const SIGNATURE_ACK_FAILURE = 0x0f;

    const SIGNATURE_DISCARD_ALL = 0x2f;

    const SIGNATURE_FAILURE = 0x7f;

    const SIGNATURE_IGNORE = 0x7E;

    const SIGNATURE_INIT = 0x01;

    const SIGNATURE_PULL_ALL = 0x3f;

    const SIGNATURE_RECORD = 0x71;
    // SIGNATURES

    const SIGNATURE_RUN = 0x10;

    const SIGNATURE_SUCCESS = 0x70;
}
