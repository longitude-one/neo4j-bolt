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

class Marker
{
    public const END = 0x00;

    public const FALSE = 0xC2;

    public const FLOAT_64 = 0xC1;

    public const INT_16 = 0xC9;
    public const INT_32 = 0xCA;
    public const INT_64 = 0xCB;
    public const INT_8 = 0xC8;

    public const LIST_16 = 0xd5;
    public const LIST_32 = 0xd6;
    public const LIST_8 = 0xd4;
    public const LIST_TINY = 0x90;

    public const MAP_16 = 0xd9;
    public const MAP_32 = 0xda;
    public const MAP_8 = 0xd8;
    public const MAP_TINY = 0xa0;

    public const NULL = 0xC0;

    public const STRUCTURE_LARGE = 0xdd;
    public const STRUCTURE_MEDIUM = 0xdc;
    public const STRUCTURE_TINY = 0xb0;

    public const TEXT_16 = 0xd1;   //This marker is followed by string length then by string
    public const TEXT_32 = 0xd2;   //This marker is followed by string length then by string
    public const TEXT_8 = 0xd0;    //This marker is followed by string length then by string
    public const TEXT_TINY = 0x80; //This Marker isn't sent as it. Length is added.

    public const TRUE = 0xC3;
}
