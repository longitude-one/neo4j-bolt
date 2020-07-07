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

use GraphAware\Bolt\Exception\BoltInvalidArgumentException;
use GraphAware\Bolt\Exception\SerializationException;
use GraphAware\Bolt\Protocol\Constants\Marker;
use GraphAware\Bolt\Protocol\Constants\Signature;
use GraphAware\Common\Collection\ArrayList;
use GraphAware\Common\Collection\Map;
use OutOfBoundsException;

class Packer
{
//    public const BYTES_16 = 0xCD;
//    public const BYTES_32 = 0xCE;
//    public const BYTES_8 = 0xCC;
//
//    public const RESERVED_C4 = 0xC4;
//    public const RESERVED_C5 = 0xC5;
//    public const RESERVED_C6 = 0xC6;
//    public const RESERVED_C7 = 0xC7;
//    public const RESERVED_CF = 0xCF;
//    public const RESERVED_D3 = 0xD3;
//    public const RESERVED_D7 = 0xD7;
//    public const RESERVED_DB = 0xDB;
//    public const RESERVED_DE = 0xDE;
//    public const RESERVED_DF = 0xDF;
//    public const RESERVED_E0 = 0xE0;
//    public const RESERVED_E1 = 0xE1;
//    public const RESERVED_E2 = 0xE2;
//    public const RESERVED_E3 = 0xE3;
//    public const RESERVED_E4 = 0xE4;
//    public const RESERVED_E5 = 0xE5;
//    public const RESERVED_E6 = 0xE6;
//    public const RESERVED_E7 = 0xE7;
//    public const RESERVED_E8 = 0xE8;
//    public const RESERVED_E9 = 0xE9;
//    public const RESERVED_EA = 0xEA;
//    public const RESERVED_EB = 0xEB;
//    public const RESERVED_EC = 0xEC;
//    public const RESERVED_ED = 0xED;
//    public const RESERVED_EE = 0xEE;
//    public const RESERVED_EF = 0xEF;

    private const MINUS_2_TO_THE_04 = -16;
    private const MINUS_2_TO_THE_07 = -128;
    private const MINUS_2_TO_THE_15 = -32768;
    private const MINUS_2_TO_THE_31 = -2147483648;

    private const PACK_TO_UNSIGNED_BYTE = 'c';
    private const PACK_TO_UNSIGNED_INT = 'N';
    private const PACK_TO_UNSIGNED_LONG = 'J';
    private const PACK_TO_UNSIGNED_SHORT = 'n';

    private const PLUS_2_TO_THE_07 = 128;
    private const PLUS_2_TO_THE_15 = 32768;
    private const PLUS_2_TO_THE_31 = 2147483648;

    public static function getEndSignature(): string
    {
        return str_repeat(chr(Marker::END), 2);
    }

    public static function getRunSignature(): string //FIXME Update and test
    {
        return chr(Signature::SIGNATURE_RUN);
    }

    public static function isList(array $array): bool
    {
        foreach ($array as $k => $v) {
            if (!is_int($k)) {
                return false;
            }
        }

        return true;
    }

    public static function pack($value): string
    {
        if (null === $value) {
            return self::packNull();
        }

        if (is_bool($value)) {
            return self::packBoolean($value);
        }

        if (is_int($value)) {
            return self::packInteger($value);
        }

        if (is_float($value)) {
            return self::packFloat($value);
        }

        if (is_string($value)) {
            return self::packText($value);
        }

        if ($value instanceof Map) {
            return self::packMap($value->getElements());
        }

        if ($value instanceof ArrayList) {
            return self::packList($value->getElements());
        }

        //FIXME Try to do it
//        if ($value instanceof Structure) {
//            return self::packStructure($value);
//        }

        if (is_array($value)) {
            if (self::isList($value) && !empty($value)) {
                return self::packList($value);
            }

            return self::packMap($value);
        }

        throw new BoltInvalidArgumentException(sprintf('Could not pack the value %s', $value));
    }

    public static function packSizeMarker($stream): string
    {
        $size = mb_strlen($stream, 'ASCII');

        return pack('n', $size);
    }

    public static function packStructureHeader(int $length, $signature): string
    {
        $packedSig = chr($signature);
        if ($length < Size::SIZE_TINY) {
            return chr(Marker::STRUCTURE_TINY + $length).$packedSig;
        }

        if ($length < Size::SIZE_MEDIUM) {
            $stream = chr(Marker::STRUCTURE_MEDIUM);
            $stream .= self::packUnsignedByte($length);
            $stream .= $packedSig;

            return $stream;
        }

        if ($length < Size::SIZE_LARGE) {
            $stream = chr(Marker::STRUCTURE_LARGE);
            $stream .= self::packSignedShort($length);
            $stream .= $packedSig;

            return $stream;
        }

        throw new SerializationException(sprintf('Unable pack the size "%d" of the structure, Out of bound !', $length));
    }

    private static function getListSizeMarker(int $size): string
    {
        if ($size < Size::SIZE_TINY) {
            return chr(Marker::LIST_TINY + $size);
        }

        if ($size < Size::SIZE_8) {
            return chr(Marker::LIST_8).self::packUnsignedByte($size);
        }

        if ($size < Size::SIZE_16) {
            return chr(Marker::LIST_16).self::packUnsignedShort($size);
        }

        if ($size < Size::SIZE_32) {
            return chr(Marker::LIST_32).self::packUnsignedInteger($size);
        }

        throw new SerializationException(sprintf('Unable to create marker for List size %d', $size));
    }

    /**
     * @param int $size
     *
     * @return string
     */
    private static function getMapSizeMarker($size)
    {
        if ($size < Size::SIZE_TINY) {
            return chr(Marker::MAP_TINY + $size);
        }

        if ($size < Size::SIZE_8) {
            return chr(Marker::MAP_8).self::packUnsignedByte($size);
        }

        if ($size < Size::SIZE_16) {
            return chr(Marker::MAP_16).self::packUnsignedShort($size);
        }

        if ($size < Size::SIZE_32) {
            return chr(Marker::MAP_32).self::packUnsignedInteger($size);
        }

        throw new SerializationException(sprintf('Unable to pack Array with size %d. Out of bound !', $size));
    }

    private static function packBoolean(bool $boolean): string
    {
        return chr($boolean ? Marker::TRUE : Marker::FALSE);
    }

    /**
     * @param $v
     *
     * @return int|string
     */
    private static function packFloat(float $v)
    {
        //FIXME VERIFIER LES LONGUEURS, ON PEUT AVOIR DES COUACS
        return chr(Marker::FLOAT_64).pack('E', $v);
    }

    private static function packInteger(int $value): string
    {
        if ($value >= self::MINUS_2_TO_THE_04 && $value < self::PLUS_2_TO_THE_07) {
            return pack(self::PACK_TO_UNSIGNED_BYTE, $value);
        }

        if ($value >= self::MINUS_2_TO_THE_07 && $value < self::MINUS_2_TO_THE_04) {
            return chr(Marker::INT_8).pack(self::PACK_TO_UNSIGNED_BYTE, $value);
        }

        if ($value >= self::MINUS_2_TO_THE_15 && $value < self::PLUS_2_TO_THE_15) {
            return chr(Marker::INT_16).pack(self::PACK_TO_UNSIGNED_SHORT, $value);
        }

        if ($value >= self::MINUS_2_TO_THE_31 && $value < self::PLUS_2_TO_THE_31) {
            return  chr(Marker::INT_32).pack(self::PACK_TO_UNSIGNED_INT, $value);
        }

        return chr(Marker::INT_64).pack(self::PACK_TO_UNSIGNED_LONG, $value);
    }

    private static function packList(array $list): string
    {
        $size = count($list);
        $bytes = self::getListSizeMarker($size);
        foreach ($list as $key => $value) {
            $bytes .= self::pack($value);
        }

        return $bytes;
    }

    private static function packMap(array $map): string
    {
        $size = count($map);
        $b = '';
        $b .= self::getMapSizeMarker($size);

        foreach ($map as $k => $v) {
            $b .= self::pack($k);
            $b .= self::pack($v);
        }

        return $b;
    }

    private static function packNull(): string
    {
        return chr(Marker::NULL);
    }

    private static function packSignedByte($value): string
    {
        return pack('c', $value);
    }

    /**
     * @param int $integer
     *
     * @return string
     */
    private static function packSignedShort($integer)
    {
        $p = pack('s', $integer);
        $v = ord($p);
//        var_dump($v);
//        var_dump($v >> 32);FIXME compare with the s solution

        return $v >> 32;
        //return pack('s', $integer); ALEX
    }

    /**
     * @param mixed $value
     *
     * @throws OutOfBoundsException
     */
    private static function packText($value): string
    {
        $length = strlen($value);

        if ($length < Size::SIZE_TINY) {
            return chr(Marker::TEXT_TINY + $length).$value;
        }

        if ($length < Size::SIZE_8) {
            return chr(Marker::TEXT_8).self::packUnsignedByte($length).$value;
        }

        if ($length < Size::SIZE_16) {
            return chr(Marker::TEXT_16).self::packUnsignedShort($length).$value;
        }

        if ($length < 2147483643) {
            return chr(Marker::TEXT_32).self::packUnsignedInteger($length).$value;
        }

        if (4 === PHP_INT_SIZE) {
            throw new OutOfBoundsException(sprintf('String size overflow, this 32bits version of PHP is limited to %d, you gave a string of size %d. You should deploy a 64bits PHP version to use so long string.', 2147483647, $length));
        }

        if ($length <= Size::SIZE_32) {
            return chr(Marker::TEXT_32).self::packUnsignedInteger($length).$value;
        }

        throw new OutOfBoundsException(sprintf('String size overflow, Bolt protocol is limited to %d, you provided a string of size %d.', Size::SIZE_32, $length));
    }

    /**
     * @param int $integer
     *
     * @return string
     */
    private static function packUnsignedByte($integer)
    {
        return pack('C', $integer); //C = Caractère non signé
    }

    private static function packUnsignedInteger($value): string
    {
        return pack('N', $value);
    }

    private static function packUnsignedLong($value): string
    {
        return pack('J', $value);
    }

    private static function packUnsignedShort($value): string
    {
        return pack('n', $value);
    }
}
