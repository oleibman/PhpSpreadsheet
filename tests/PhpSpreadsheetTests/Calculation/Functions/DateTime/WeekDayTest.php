<?php

declare(strict_types=1);

namespace PhpOffice\PhpSpreadsheetTests\Calculation\Functions\DateTime;

use PhpOffice\PhpSpreadsheet\Calculation\Calculation;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Week;
use PhpOffice\PhpSpreadsheet\Shared\Date as SharedDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheetTests\Calculation\Functions\FormulaArguments;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class WeekDayTest extends TestCase
{
    private int $excelCalendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->excelCalendar = SharedDate::getExcelCalendar();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        SharedDate::setExcelCalendar($this->excelCalendar);
    }

    #[DataProvider('providerWEEKDAY')]
    public function testDirectCallToWEEKDAY(int|string $expectedResult, bool|int|string $dateValue, null|int|string $style = null): void
    {
        $result = ($style === null) ? Week::day($dateValue) : Week::day($dateValue, $style);
        self::assertSame($expectedResult, $result);
    }

    #[DataProvider('providerWEEKDAY')]
    public function testWEEKDAYAsFormula(mixed $expectedResult, mixed ...$args): void
    {
        $arguments = new FormulaArguments(...$args);

        $calculation = Calculation::getInstance();
        $formula = "=WEEKDAY({$arguments})";

        $result = $calculation->_calculateFormulaValue($formula);
        self::assertSame($expectedResult, $result);
    }

    #[DataProvider('providerWEEKDAY')]
    public function testWEEKDAYInWorksheet(mixed $expectedResult, mixed ...$args): void
    {
        $arguments = new FormulaArguments(...$args);

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $argumentCells = $arguments->populateWorksheet($worksheet);
        $formula = "=WEEKDAY({$argumentCells})";

        $result = $worksheet->setCellValue('A1', $formula)
            ->getCell('A1')
            ->getCalculatedValue();
        self::assertSame($expectedResult, $result);

        $spreadsheet->disconnectWorksheets();
    }

    public static function providerWEEKDAY(): array
    {
        return require 'tests/data/Calculation/DateTime/WEEKDAY.php';
    }

    #[DataProvider('providerUnhappyWEEKDAY')]
    public function testWEEKDAYUnhappyPath(string $expectedException, mixed ...$args): void
    {
        $arguments = new FormulaArguments(...$args);

        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $argumentCells = $arguments->populateWorksheet($worksheet);
        $formula = "=WEEKDAY({$argumentCells})";

        $this->expectException(\PhpOffice\PhpSpreadsheet\Calculation\Exception::class);
        $this->expectExceptionMessage($expectedException);
        $worksheet->setCellValue('A1', $formula)
            ->getCell('A1')
            ->getCalculatedValue();

        $spreadsheet->disconnectWorksheets();
    }

    public static function providerUnhappyWEEKDAY(): array
    {
        return [
            ['Formula Error: Wrong number of arguments for WEEKDAY() function'],
        ];
    }

    public function testWEEKDAYWith1904Calendar(): void
    {
        SharedDate::setExcelCalendar(SharedDate::CALENDAR_MAC_1904);

        self::assertEquals(7, Week::day('1904-01-02'));
        self::assertEquals(6, Week::day('1904-01-01'));
        self::assertEquals(6, Week::day(null));
    }

    /** @param mixed[] $expectedResult */
    #[DataProvider('providerWeekDayArray')]
    public function testWeekDayArray(array $expectedResult, string $dateValues, string $styles): void
    {
        $calculation = Calculation::getInstance();

        $formula = "=WEEKDAY({$dateValues}, {$styles})";
        $result = $calculation->_calculateFormulaValue($formula);
        self::assertEqualsWithDelta($expectedResult, $result, 1.0e-14);
    }

    public static function providerWeekDayArray(): array
    {
        return [
            'row vector #1' => [[[7, 1, 7]], '{"2022-01-01", "2022-06-12", "2023-07-22"}', '1'],
            'column vector #1' => [[[1], [7], [7]], '{"2023-01-01"; "2023-04-01"; "2023-07-01"}', '1'],
            'matrix #1' => [[[6, 6], [1, 1]], '{"2021-01-01", "2021-12-31"; "2023-01-01", "2023-12-31"}', '1'],
            'row vector #2' => [[[7, 6]], '"2022-01-01"', '{1, 2}'],
            'column vector #2' => [[[1], [7]], '"2023-01-01"', '{1; 2}'],
        ];
    }
}
