<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxExport
{
    /** Một sheet: dòng tiêu đề in đậm, rồi từng dòng (mảng giá trị hoặc Row có sẵn style) */
    public static function download(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $writer = new Writer();
            $writer->openToFile('php://output');
            $writer->addRow(self::bold($headings));
            foreach ($rows as $row) {
                $writer->addRow($row instanceof Row ? $row : Row::fromValues($row));
            }
            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public static function bold(array $values): Row
    {
        return Row::fromValues($values, (new Style())->setFontBold());
    }
}
