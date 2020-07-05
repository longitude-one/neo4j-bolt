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

use GraphAware\Bolt\PackStream\Packer;
use GraphAware\Common\Collection\ArrayList;
use GraphAware\Common\Collection\Map;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class PackerTest extends TestCase
{
    private static function assertBytes(string $expected, $actual)
    {
        $byte = '';
        foreach (explode(' ', $expected) as $byteCode) {
            $byte .= hex2bin($byteCode);
        }

        self::assertSame($byte, $actual, sprintf(
            'Failed asserting that two bytes are identical %s and %s',
            $expected,
            implode(' ', unpack('H*', $actual))
        ));
    }

    public function integerProvider()
    {
        $csv = fopen(__DIR__.'/../../Resources/integers.csv', 'r');
        while ($data = fgetcsv($csv, 0, ',')) {
            yield 'Integer '.$data[1] => $data;
        }
        fclose($csv);
    }

    public function testPackFalse(): void
    {
        self::assertBytes('C2', Packer::pack(false));
    }

    public function testPackFloat(): void
    {
        self::assertBytes('C1 3F F1 99 99 99 99 99 9A', Packer::pack(1.1));
        self::assertBytes('C1 BF F1 99 99 99 99 99 9A', Packer::pack(-1.1));
    }

    /**
     * @dataProvider integerProvider
     *
     * @param mixed $integer
     */
    public function testPackInteger(string $expected, $integer): void
    {
        if ($integer < PHP_INT_MIN || $integer > PHP_INT_MAX) {
            self::markTestIncomplete('This workstation is running on a PHP32bit, I cannot complete this test.');
        }

        self::assertBytes($expected, Packer::pack((int) $integer));
    }

    public function testPackList(): void
    {
        self::assertBytes('90', Packer::pack(new ArrayList([])));
        self::assertBytes('93 01 02 03', Packer::pack(new ArrayList(range(1, 3))));
        $actual = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 0]);
        self::assertBytes('D4 14 01 02 03 04 05 06 07 08 09 00 01 02 03 04 05 06 07 08 09 00', Packer::pack($actual));
    }

    public function testPackLongText(): void
    {
        if ('-1' != ini_get('memory_limit')) {
            self::markTestSkipped('Memory should have no limit to perform this test.');
        }

        if (4 === PHP_INT_SIZE) {
            self::expectException(OutOfBoundsException::class);
            self::expectExceptionMessage('String size overflow, this 32bits version of PHP is limited to %d, you gave a string of size %d. You should deploy a 64bits PHP version to use so long string.');
        }

        self::markTestIncomplete('I should create a group');
//        Packer::pack(str_repeat('aa', (2147483646)/2 + 10));
    }

    public function testPackMap(): void
    {
        self::assertBytes('A0', Packer::pack(new Map([])));
        self::assertBytes('A1 81 61 01', Packer::pack(new Map(['a' => 1])));
        $actual = new Map([
            'a' => 1,
            'b' => 1,
            'c' => 3,
            'd' => 4,
            'e' => 5,
            'f' => 6,
            'g' => 7,
            'h' => 8,
            'i' => 9,
            'j' => 0,
            'k' => 1,
            'l' => 2,
            'm' => 3,
            'n' => 4,
            'o' => 5,
            'p' => 6,
        ]);
        self::assertBytes('D8 10 81 61 01 81 62 01 81 63 03 81 64 04 81 65 05 81 66 06 81 67 07 81 68 08 81 69 09 81 6A 00 81 6B 01 81 6C 02 81 6D 03 81 6E 04 81 6F 05 81 70 06', Packer::pack($actual));
    }

    public function testPackNull(): void
    {
        self::assertBytes('C0', Packer::pack(null));
    }

    public function testPackText(): void
    {
        self::assertBytes('81 61', Packer::pack('a'));
        self::assertBytes('D0 1A 61 62 63 64 65 66 67 68 69 6A 6B 6C 6D 6E 6F 70 71 72 73 74 75 76 77 78 79 7A', Packer::pack('abcdefghijklmnopqrstuvwxyz'));
        self::assertBytes('D0 18 45 6E 20 C3 A5 20 66 6C C3 B6 74 20 C3 B6 76 65 72 20 C3 A4 6E 67 65 6E', Packer::pack('En å flöt över ängen'));
    }

    public function testPackTrue(): void
    {
        self::assertBytes('C3', Packer::pack(true));
    }
}
