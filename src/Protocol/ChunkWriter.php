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

namespace GraphAware\Bolt\Protocol;

use GraphAware\Bolt\IO\AbstractIO;
use GraphAware\Bolt\PackStream\Packer;

class ChunkWriter
{
    const MAX_CHUNK_SIZE = 8192;

    /**
     * @var AbstractIO
     */
    protected $io;

    /**
     * @var Packer
     */
    protected $packer;

    public function __construct(AbstractIO $io, Packer $packer)
    {
        $this->io = $io;
        $this->packer = $packer;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    public function splitChunk($data)
    {
        return str_split($data, self::MAX_CHUNK_SIZE);
    }

    /**
     * @param \GraphAware\Bolt\Protocol\Message\AbstractMessage[] $messages
     */
    public function writeMessages(array $messages)
    {
        $raw = '';

        foreach ($messages as $msg) {
            $chunkData = $msg->getSerialization();
            $chunks = $this->splitChunk($chunkData);
            foreach ($chunks as $chunk) {
                $raw .= Packer::packSizeMarker($chunk);
                $raw .= $chunk;
            }
            $raw .= Packer::getEndSignature();
        }

        $this->io->write($raw);
    }
}
