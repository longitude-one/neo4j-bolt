<?php
/**
 * This file is part of the LongitudeOne Neo4j Bolt driver for PHP.
 *
 * PHP version 7.2|7.3|7.4
 * Neo4j 3.0|3.5|4.0|4.1
 *
 * (c) Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * (c) Longitude One 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace GraphAware\Bolt\PackStream;

class Size
{
    const SIZE_16 = 65536;

    const SIZE_32 = 4294967295;

    const SIZE_8 = 256;

    const SIZE_LARGE = 65536;

    const SIZE_MEDIUM = 256;
    const SIZE_TINY = 16;
}
