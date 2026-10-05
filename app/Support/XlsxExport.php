<?php

namespace App\Support;

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
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
                $writer->addRow($row instanceof Row ? $row : self::row($row));
            }
            $writer->close();
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public static function bold(array $values): Row
    {
        return self::row($values, (new Style())->setFontBold());
    }

    /** Chuỗi luôn ghi thành chữ: Cell::fromValue biến chuỗi bắt đầu bằng "=" thành công thức (tên khách tự đặt được) */
    private static function row(array $values, ?Style $style = null): Row
    {
        return new Row(array_map(fn ($v) => is_string($v) ? new StringCell($v, null) : Cell::fromValue($v), $values), $style);
    }
}
