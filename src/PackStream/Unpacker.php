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

namespace GraphAware\Bolt\PackStream;

use GraphAware\Bolt\Exception\SerializationException;
use GraphAware\Bolt\Misc\Helper;
use GraphAware\Bolt\PackStream\Structure\Structure;
use GraphAware\Bolt\Protocol\Constants;
use GraphAware\Bolt\Protocol\Message\RawMessage;
use GraphAware\Bolt\Result\Type\Node;
use GraphAware\Bolt\Result\Type\Path;
use GraphAware\Bolt\Result\Type\Relationship;

class Unpacker
{
    const FAILURE = 'FAILURE';

    const IGNORED = 'IGNORED';

    const INIT = 'INIT';

    const RECORD = 'RECORD';
    const SUCCESS = 'SUCCESS';

    /**
     * @var bool
     */
    protected $is64bits;

    /**
     * @var StreamChannel
     */
    protected $streamChannel;

    public function __construct(StreamChannel $streamChannel)
    {
        $this->is64bits = PHP_INT_SIZE == 8;
        $this->streamChannel = $streamChannel;
    }

    /**
     * @param int $longInt
     *
     * @return bool
     */
    private static function getLongMSB($longInt)
    {
        return (bool) ($longInt & 0x80000000);
    }

    /**
     * @param string $byte
     *
     * @return int
     */
    public function getLowNibbleValue($byte)
    {
        $marker = ord($byte);

        return $marker & 0x0f;
    }

    /**
     * @return string
     */
    public function getSignature(BytesWalker $walker)
    {
        static $signatures = [
            Constants::SIGNATURE_INIT => self::INIT,
            Constants::SIGNATURE_SUCCESS => self::SUCCESS,
            Constants::SIGNATURE_FAILURE => self::FAILURE,
            Constants::SIGNATURE_RECORD => self::RECORD,
            Constants::SIGNATURE_IGNORE => self::IGNORED,
            Constants::SIGNATURE_UNBOUND_RELATIONSHIP => 'UNBOUND_RELATIONSHIP',
            Constants::SIGNATURE_NODE => 'NODE',
            Constants::SIGNATURE_PATH => 'PATH',
            Constants::SIGNATURE_RELATIONSHIP => 'RELATIONSHIP',
        ];

        $sigMarker = $walker->read(1);
        $ordMarker = ord($sigMarker);

        return $signatures[$ordMarker];

//        if (Constants::SIGNATURE_SUCCESS === $ordMarker) {
//            return self::SUCCESS;
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_FAILURE, $sigMarker)) {
//            return self::FAILURE;
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_RECORD, $sigMarker)) {
//            return self::RECORD;
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_IGNORE, $sigMarker)) {
//            return self::IGNORED;
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_UNBOUND_RELATIONSHIP, $sigMarker)) {
//            return "UNBOUND_RELATIONSHIP";
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_NODE, $sigMarker)) {
//            return "NODE";
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_PATH, $sigMarker)) {
//            return "PATH";
//        }
//
//        if ($this->isSignature(Constants::SIGNATURE_RELATIONSHIP, $sigMarker)) {
//            return "RELATIONSHIP";
//        }
//
//        throw new SerializationException(sprintf('Unable to guess the signature for byte "%s"', Helper::prettyHex($sigMarker)));
    }

    /**
     * @return int
     */
    public function getStructureSize(BytesWalker $walker)
    {
        $marker = $walker->read(1);

        // if tiny size, no more bytes to read, the size is encoded in the low nibble
        if ($this->isMarkerHigh($marker, Constants::STRUCTURE_TINY)) {
            return $this->getLowNibbleValue($marker);
        }
    }

    /**
     * @param int    $start
     * @param int    $end
     * @param string $byte
     *
     * @return mixed
     */
    public function isInRange($start, $end, $byte)
    {
        $range = range($start, $end);

        return in_array(ord($byte), $range, true);
    }

    /**
     * @param $byte
     * @param $nibble
     *
     * @return bool
     */
    public function isMarker($byte, $nibble)
    {
        $marker_raw = hexdec(bin2hex($byte));

        return $marker_raw === $nibble;
    }

    /**
     * @param $byte
     * @param $nibble
     *
     * @return bool
     */
    public function isMarkerHigh($byte, $nibble)
    {
        $marker_raw = ord($byte);
        $marker = $marker_raw & 0xF0;

        return $marker === $nibble;
    }

    /**
     * @param $sig
     * @param $byte
     *
     * @return bool
     */
    public function isSignature($sig, $byte)
    {
        return $sig === ord($byte);
    }

    /**
     * @return string
     */
    public function read_longlong(BytesWalker $walker)
    {
        $this->bitcount = $this->bits = 0;
        list(, $hi, $lo) = unpack('N2', $walker->read(8));
        $msb = self::getLongMSB($hi);

        if (!$this->is64bits) {
            if ($msb) {
                $hi = sprintf('%u', $hi);
            }
            if (self::getLongMSB($lo)) {
                $lo = sprintf('%u', $lo);
            }
        }

        return bcadd($this->is64bits && !$msb ? $hi << 32 : bcmul($hi, '4294967296', 0), $lo, 0);
    }

    /**
     * @return mixed
     */
    public function readSignedLong(BytesWalker $walker)
    {
        list(, $v) = unpack('l', $this->correctEndianness($walker->read(4)));

        return $v;
    }

    /**
     * @return int
     */
    public function readSignedLongLong(BytesWalker $walker)
    {
        list(, $high, $low) = unpack('N2', $walker->read(8));

        return (int) bcadd((string) ($high << 32), (string) $low, 0);
    }

    /**
     * @return mixed
     */
    public function readSignedShort(BytesWalker $walker)
    {
        list(, $v) = unpack('s', $this->correctEndianness($walker->read(2)));

        return $v;
    }

    /**
     * @return mixed
     */
    public function readSignedShortShort(BytesWalker $walker)
    {
        list(, $v) = unpack('c', $walker->read(1));

        return $v;
    }

    /**
     * @return mixed
     */
    public function readUnsignedLong(BytesWalker $walker)
    {
        list(, $v) = unpack('N', $walker->read(4));

        return sprintf('%u', $v);
    }

    /**
     * @return mixed
     */
    public function readUnsignedLongLong(BytesWalker $walker)
    {
        list(, $v) = unpack('J', $walker->read(8));

        return $v;
    }

    /**
     * @return mixed
     */
    public function readUnsignedShort(BytesWalker $walker)
    {
        list(, $v) = unpack('n', $walker->read(2));

        return $v;
    }

    /**
     * @return mixed
     */
    public function readUnsignedShortShort(BytesWalker $walker)
    {
        list(, $v) = unpack('C', $walker->read(1));

        return $v;
    }

    /**
     * @return Structure
     */
    public function unpack()
    {
        $b = '';
        do {
            $chunkHeader = $this->streamChannel->read(2);
            list(, $size) = unpack('n', $chunkHeader);
            $b .= $this->streamChannel->read($size);
        } while ($size > 0);

        return $this->unpackElement(new BytesWalker(new RawMessage($b)));
    }

    public function unpackElement(BytesWalker $walker)
    {
        $marker = $walker->read(1);
        $byte = hexdec(bin2hex($marker));
        $ordMarker = ord($marker);
        $markerHigh = $ordMarker & 0xf0;
        $markerLow = $ordMarker & 0x0f;

        // Structures
        if (0xb0 <= $ordMarker && $ordMarker <= 0xbf) {
            $walker->rewind(1);
            $structureSize = $this->getStructureSize($walker);
            $sig = $this->getSignature($walker);
            $str = new Structure($sig, $structureSize);
            $done = 0;
            while ($done < $structureSize) {
                $elt = $this->unpackElement($walker);
                $str->addElement($elt);
                ++$done;
            }

            return $str;
        }

        if (Constants::MAP_TINY === $markerHigh) {
            $size = $markerLow;
            $map = [];
            for ($i = 0; $i < $size; ++$i) {
                $identifier = $this->unpackElement($walker);
                $value = $this->unpackElement($walker);
                $map[$identifier] = $value;
            }

            return $map;
        }

        if (Constants::MAP_8 === $byte) {
            $size = $this->readUnsignedShortShort($walker);

            return $this->unpackMap($size, $walker);
        }

        if (Constants::MAP_16 === $byte) {
            $size = $this->readUnsignedShort($walker);

            return $this->unpackMap($size, $walker);
        }

        if (Constants::MAP_32 === $byte) {
            $size = $this->readUnsignedLong($walker);

            return $this->unpackMap($size, $walker);
        }

        if (Constants::TEXT_TINY === $markerHigh) {
            $textSize = $this->getLowNibbleValue($marker);

            return $this->unpackText($textSize, $walker);
        }

        if (Constants::TEXT_8 === $byte) {
            $textSize = $this->readUnsignedShortShort($walker);

            return $this->unpackText($textSize, $walker);
        }

        if (Constants::TEXT_16 === $byte) {
            $textSize = $this->readUnsignedShort($walker);

            return $this->unpackText($textSize, $walker);
        }

        if (Constants::TEXT_32 === $byte) {
            $textSize = $this->readUnsignedLong($walker);

            return $this->unpackText($textSize, $walker);
        }

        if (Constants::INT_8 === $byte) {
            $integer = $this->readSignedShortShort($walker);

            return $this->unpackInteger($integer);
        }

        if (Constants::INT_16 === $byte) {
            $integer = $this->readSignedShort($walker);

            return $this->unpackInteger($integer);
        }

        if (Constants::INT_32 === $byte) {
            $integer = $this->readSignedLong($walker);

            return $this->unpackInteger($integer);
        }

        if (Constants::INT_64 === $byte) {
            $integer = $this->readSignedLongLong($walker);

            return $this->unpackInteger($integer);
        }

        if (Constants::LIST_TINY === $markerHigh) {
            $size = $this->getLowNibbleValue($marker);

            return $this->unpackList($size, $walker);
        }

        if (Constants::LIST_8 === $byte) {
            $size = $this->readUnsignedShortShort($walker);

            return $this->unpackList($size, $walker);
        }

        if (Constants::LIST_16 === $byte) {
            $size = $this->readUnsignedShort($walker);

            return $this->unpackList($size, $walker);
        }

        if (Constants::LIST_32 === $byte) {
            $size = $this->readUnsignedLong($walker);

            return $this->unpackList($size, $walker);
        }

        // Checks for TINY INTS
        if ($this->isInRange(0x00, 0x7f, $marker) || $this->isInRange(0xf0, 0xff, $marker)) {
            $walker->rewind(1);
            $integer = $this->readSignedShortShort($walker);

            return $this->unpackInteger($integer);
        }

        // Checks for floats
        if (Constants::MARKER_FLOAT === $byte) {
            list(, $v) = unpack('d', strrev($walker->read(8)));

            return (float) $v;
        }

        // Checks Primitive Values NULL, TRUE, FALSE
        if (Constants::MARKER_NULL === $byte) {
            return null;
        }

        if (Constants::MARKER_TRUE === $byte) {
            return true;
        }

        if (Constants::MARKER_FALSE === $byte) {
            return false;
        }

        throw new SerializationException(sprintf('Unable to find serialization type for marker %s', Helper::prettyHex($marker)));
    }

    /**
     * @param string $value
     *
     * @return int
     */
    public function unpackInteger($value)
    {
        return (int) $value;
    }

    /**
     * @param int $size
     *
     * @return array
     */
    public function unpackList($size, BytesWalker $walker)
    {
        $size = (int) $size;
        $list = [];
        for ($i = 0; $i < $size; ++$i) {
            $list[] = $this->unpackElement($walker);
        }

        return $list;
    }

    /**
     * @param int $size
     *
     * @return array
     */
    public function unpackMap($size, BytesWalker $walker)
    {
        $map = [];
        for ($i = 0; $i < $size; ++$i) {
            $identifier = $this->unpackElement($walker);
            $value = $this->unpackElement($walker);
            $map[$identifier] = $value;
        }

        return $map;
    }

    /**
     * @return Node
     */
    public function unpackNode(BytesWalker $walker)
    {
        $identity = $this->unpackElement($walker);
        $labels = $this->unpackElement($walker);
        $properties = $this->unpackElement($walker);

        return new Node($identity, $labels, $properties);
    }

    /**
     * @return Path
     */
    public function unpackPath(BytesWalker $walker)
    {
        return $this->unpackElement($walker);
    }

    /**
     * @return \GraphAware\Bolt\PackStream\Structure\Structure
     */
    public function unpackRaw(RawMessage $message)
    {
        $walker = new BytesWalker($message);

        return $this->unpackElement($walker);
    }

    /**
     * @return Relationship
     */
    public function unpackRelationship(BytesWalker $walker)
    {
        $identity = $this->unpackElement($walker);
        $startNode = $this->unpackElement($walker);
        $endNode = $this->unpackElement($walker);
        $type = $this->unpackElement($walker);
        $properties = $this->unpackElement($walker);

        return new Relationship($identity, $startNode, $endNode, $type, $properties);
    }

    /**
     * @param int $size
     *
     * @return string
     */
    public function unpackText($size, BytesWalker $walker)
    {
        return $walker->read($size);
    }

    /**
     * @param string $byteString
     *
     * @return string
     */
    private function correctEndianness($byteString)
    {
        $tmp = unpack('S', "\x01\x00");
        $isLittleEndian = 1 == $tmp[1];

        return $isLittleEndian ? strrev($byteString) : $byteString;
    }
}
