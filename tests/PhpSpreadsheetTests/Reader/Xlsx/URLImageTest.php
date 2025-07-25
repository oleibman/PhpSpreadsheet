<?php

declare(strict_types=1);

namespace PhpOffice\PhpSpreadsheetTests\Reader\Xlsx;

use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheetTests\Reader\Utility\File;
use PHPUnit\Framework\TestCase;

class URLImageTest extends TestCase
{
    public function testURLImageSourceAllowed(): void
    {
        if (getenv('SKIP_URL_IMAGE_TEST') === '1') {
            self::markTestSkipped('Skipped due to setting of environment variable');
        }
        $filename = realpath(__DIR__ . '/../../../data/Reader/XLSX/urlImage.xlsx');
        self::assertNotFalse($filename);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setAllowExternalImages(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $collection = $worksheet->getDrawingCollection();
        self::assertCount(1, $collection);

        foreach ($collection as $drawing) {
            self::assertInstanceOf(Drawing::class, $drawing);
            // Check if the source is a URL or a file path
            self::assertTrue($drawing->getIsURL());
            self::assertSame('https://phpspreadsheet.readthedocs.io/en/latest/topics/images/01-03-filter-icon-1.png', $drawing->getPath());
            self::assertSame(IMAGETYPE_PNG, $drawing->getType());
            self::assertSame(84, $drawing->getWidth());
            self::assertSame(44, $drawing->getHeight());
        }
        $spreadsheet->disconnectWorksheets();
    }

    public function testURLImageSourceNotAllowed(): void
    {
        $filename = realpath(__DIR__ . '/../../../data/Reader/XLSX/urlImage.xlsx');
        self::assertNotFalse($filename);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setAllowExternalImages(false);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $collection = $worksheet->getDrawingCollection();
        self::assertCount(0, $collection);
        $spreadsheet->disconnectWorksheets();
    }

    public function testURLImageSourceNotFoundAllowed(): void
    {
        if (getenv('SKIP_URL_IMAGE_TEST') === '1') {
            self::markTestSkipped('Skipped due to setting of environment variable');
        }
        $filename = realpath(__DIR__ . '/../../../data/Reader/XLSX/urlImage.notfound.xlsx');
        self::assertNotFalse($filename);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setAllowExternalImages(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $collection = $worksheet->getDrawingCollection();
        self::assertCount(0, $collection);
        $spreadsheet->disconnectWorksheets();
    }

    public function testURLImageSourceNotFoundNotAllowed(): void
    {
        $filename = realpath(__DIR__ . '/../../../data/Reader/XLSX/urlImage.notfound.xlsx');
        self::assertNotFalse($filename);
        $reader = IOFactory::createReader('Xlsx');
        $reader->setAllowExternalImages(false);
        $spreadsheet = $reader->load($filename);
        $worksheet = $spreadsheet->getActiveSheet();
        $collection = $worksheet->getDrawingCollection();
        self::assertCount(0, $collection);
        $spreadsheet->disconnectWorksheets();
    }

    public function testURLImageSourceBadProtocol(): void
    {
        $filename = realpath(__DIR__ . '/../../../data/Reader/XLSX/urlImage.bad.dontuse');
        self::assertNotFalse($filename);
        $this->expectException(SpreadsheetException::class);
        $this->expectExceptionMessage('Invalid protocol for linked drawing');
        $reader = IOFactory::createReader('Xlsx');
        $reader->load($filename);
    }
}
