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

namespace GraphAware\Bolt\Tests\Unit\PackStream;

use GraphAware\Bolt\IO\StreamSocket;
use GraphAware\Bolt\PackStream\BytesWalker;
use GraphAware\Bolt\PackStream\Marker;
use GraphAware\Bolt\PackStream\Packer;
use GraphAware\Bolt\PackStream\Size;
use GraphAware\Bolt\PackStream\StreamChannel;
use GraphAware\Bolt\PackStream\Unpacker;
use GraphAware\Bolt\Protocol\Constants;
use GraphAware\Bolt\Protocol\Message\RawMessage;
use PHPUnit\Framework\TestCase;

/**
 * Class UnpackerTest.
 *
 * @group unit
 * @group unpack
 *
 * @internal
 * @coversNothing
 */
class PackingUnpackingTest extends TestCase
{
    /**
     * @var Unpacker
     */
    protected $unpacker;

    public function setUp(): void
    {
        $this->unpacker = new Unpacker(new StreamChannel(new StreamSocket('bolt://localhost', 7687)));
    }

    /**
     * @group sig
     */
    public function testGetSignature()
    {
        $bytes = hex2bin('b170a0');
        $raw = new RawMessage($bytes);
        $walker = new BytesWalker($raw);
        $walker->forward(1);

        $sig = $this->unpacker->getSignature($walker);
        $this->assertSame('SUCCESS', $sig);
    }

    public function testPackingFalse()
    {
        $w = $this->getWalkerForBinary(chr(Constants::MARKER_FALSE));
        $this->assertFalse($this->unpacker->unpackElement($w));
        $this->assertSame(chr(Marker::FALSE), Packer::pack(false));
    }

    public function testPackingNull()
    {
        $w = $this->getWalkerForBinary(chr(Constants::MARKER_NULL));
        $this->assertNull($this->unpacker->unpackElement($w));
        $this->assertSame(chr(Marker::NULL), Packer::pack(null));
    }

    public function testPackingText16()
    {
        $text = str_repeat('a', (Size::SIZE_16) - 1);
        $binary = chr(0xd1).chr(0xFF).chr(0xFF).$text;
        $w = $this->getWalkerForBinary($binary);
        $this->assertSame($text, $this->unpacker->unpackElement($w));
        $this->assertSame($binary, Packer::pack($text));
    }

    public function testPackingText8()
    {
        $text = str_repeat('a', (Size::SIZE_8) - 1);
        $binary = chr(0xd0).chr(0xFF).$text;
        $w = $this->getWalkerForBinary($binary);
        $this->assertSame($text, $this->unpacker->unpackElement($w));
        $this->assertSame($binary, Packer::pack($text));
    }

    public function testPackingTinyText()
    {
        $text = 'TinyText';
        $length = strlen($text);
        $binary = chr(Marker::TEXT_TINY + $length).$text;
        $w = $this->getWalkerForBinary($binary);
        $this->assertSame($text, $this->unpacker->unpackElement($w));
        $this->assertSame($binary, Packer::pack($text));
    }

    public function testPackingTrue()
    {
        $w = $this->getWalkerForBinary(chr(Constants::MARKER_TRUE));
        $this->assertTrue( $this->unpacker->unpackElement($w));
        $this->assertSame(chr(Marker::TRUE), Packer::pack(true));
    }

    /**
     * @param string $binary
     * @param int    $pos
     *
     * @return BytesWalker
     */
    protected function getWalkerForBinary($binary = '', $pos = 0)
    {
        return new BytesWalker(new RawMessage($binary), $pos);
    }
}
