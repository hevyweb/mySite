<?php

namespace App\Tests\Unit\Service;

use App\Service\ArrayService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArrayServiceTest extends TestCase
{
    private ArrayService $arrayService;

    protected function setUp(): void
    {
        $this->arrayService = new ArrayService();
    }

    #[DataProvider('integerIdsDataProvider')]
    public function testGetIntegerIds(mixed $input, array $expected): void
    {
        $this->assertSame($expected, $this->arrayService->getIntegerIds($input));
    }

    public static function integerIdsDataProvider(): \Generator
    {
        yield 'non-array string input' => ['string', []];
        yield 'non-array null input' => [null, []];
        yield 'non-array integer input' => [123, []];
        yield 'empty array' => [[], []];
        yield 'standard indexed array' => [['apple', 'banana', 'cherry'], [0, 1, 2]];
        yield 'associative array with integer-like string keys' => [['1' => 'a', '2' => 'b'], [1, 2]];
        yield 'associative array with integer keys' => [[10 => 'foo', 20 => 'bar'], [10, 20]];
    }
}
