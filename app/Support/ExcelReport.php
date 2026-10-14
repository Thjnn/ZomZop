<?php

namespace App\Support;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * File Excel có định dạng: dải tiêu đề, bảng (tiêu đề đỏ, sọc, viền, định dạng số, dòng tổng), biểu đồ.
 *   (new ExcelReport('Chấm công', 'Tháng 10/2026'))->sheet('Chi tiết')->table([...], $rows, ['C' => 'money'])->chart(...)->download('x.xlsx')
 */
class ExcelReport
{
    public const BRAND = 'EF4444';
    public const AMBER = 'FEF3C7';
    public const RED   = 'FEE2E2';
    public const GREEN = 'DCFCE7';
    private const PALETTE = ['EF4444', 'F59E0B', '10B981', '3B82F6', '8B5CF6', 'EC4899', '14B8A6', '64748B'];

    public const FORMATS = [
        'money' => '#,##0" ₫"',
        'int'   => '#,##0',
        'dec'   => '#,##0.00',
        'pct'   => '0.0"%"',
    ];

    private Spreadsheet $book;
    private ?Worksheet $sheet = null;
    private int $row = 4;
    private int $width = 1;       // số cột đã dùng trên sheet (để đặt biểu đồ bên phải)
    private int $chartRow = 4;
    private bool $frozen = false;

    public function __construct(private string $title, private string $subtitle)
    {
        $this->book = new Spreadsheet();
        $this->book->getProperties()->setCreator('ZomZop')->setTitle($title);
    }

    /** Sang sheet mới (sheet đầu dùng sheet mặc định), có dải tiêu đề 2 dòng */
    public function sheet(string $name): static
    {
        $this->sheet = $this->sheet ? $this->book->createSheet() : $this->book->getActiveSheet();
        $this->sheet->setTitle(mb_substr(str_replace(['\\', '/', '?', '*', '[', ']', ':'], ' ', $name), 0, 31));
        $this->sheet->getSheetView()->setZoomScale(110);
        $this->row = 4;
        $this->width = 1;
        $this->chartRow = 4;
        $this->frozen = false;

        $this->sheet->setCellValueExplicit('A1', $this->title, DataType::TYPE_STRING);
        $this->sheet->setCellValueExplicit('A2', $this->subtitle . ' · Xuất lúc ' . now()->format('H:i d/m/Y'), DataType::TYPE_STRING);
        $this->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB(self::BRAND);
        $this->sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('64748B');
        $this->sheet->getRowDimension(1)->setRowHeight(26);

        return $this;
    }

    /** Khối chỉ số dạng "nhãn | giá trị"; giá trị có thể là [số, 'money'|'int'|'pct'] */
    public function summary(array $pairs): static
    {
        foreach ($pairs as $label => $value) {
            [$value, $format] = is_array($value) ? $value : [$value, null];
            $this->put(1, $this->row, $label);
            $this->put(2, $this->row, $value);
            $this->sheet->getStyle("A{$this->row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            ]);
            $this->sheet->getStyle("B{$this->row}")->getFont()->setBold(true)->setSize(12);
            if ($format) {
                $this->sheet->getStyle("B{$this->row}")->getNumberFormat()->setFormatCode(self::FORMATS[$format]);
            }
            $this->sheet->getStyle("A{$this->row}:B{$this->row}")->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');
            $this->row++;
        }
        $this->width = max($this->width, 2);
        $this->row++;

        return $this;
    }

    /**
     * Bảng dữ liệu. $formats: ['C' => 'money', ...]; $totals: các cột cộng dồn ở dòng Tổng;
     * $rowColor: fn(array $row) => mã màu nền cả dòng hoặc null.
     *
     * @return array{sheet: string, header: int, first: int, last: int, cols: int}
     */
    public function table(array $headings, iterable $rows, array $formats = [], array $totals = [], ?callable $rowColor = null, ?string $caption = null): array
    {
        $s = $this->sheet;
        $cols = count($headings);
        $lastCol = Coordinate::stringFromColumnIndex($cols);

        if ($caption) {
            $this->put(1, $this->row, $caption);
            $s->getStyle("A{$this->row}")->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0F172A');
            $this->row++;
        }

        $header = $this->row;
        foreach (array_values($headings) as $i => $h) {
            $this->put($i + 1, $header, $h);
        }
        $s->getStyle("A{$header}:{$lastCol}{$header}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::BRAND]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
        $s->getRowDimension($header)->setRowHeight(22);

        $r = $header;
        foreach ($rows as $values) {
            $r++;
            foreach (array_values($values) as $i => $v) {
                $this->put($i + 1, $r, $v);
            }
            $color = $rowColor ? $rowColor($values) : null;
            if ($color || ($r - $header) % 2 === 0) {
                $s->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color ?? 'F8FAFC');
            }
        }
        $first = $header + 1;
        $last  = $r;

        if ($last >= $first) {
            foreach ($formats as $col => $fmt) {
                $s->getStyle("{$col}{$first}:{$col}" . ($last + ($totals ? 1 : 0)))->getNumberFormat()->setFormatCode(self::FORMATS[$fmt]);
            }
            if (!$this->frozen) {
                $s->setAutoFilter("A{$header}:{$lastCol}{$last}");
                $s->freezePane('A' . $first);
                $this->frozen = true;
            }
        }

        if ($totals) {
            $r++;
            $this->put(1, $r, 'Tổng');
            foreach ($totals as $col) {
                $s->setCellValue("{$col}{$r}", $last >= $first ? "=SUM({$col}{$first}:{$col}{$last})" : 0);
            }
            $s->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                'font'    => ['bold' => true],
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE4E6']],
                'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => self::BRAND]]],
            ]);
        }

        $s->getStyle("A{$header}:{$lastCol}{$r}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('E2E8F0');

        $this->width = max($this->width, $cols);
        $this->row = $r + 2;

        return ['sheet' => $s->getTitle(), 'header' => $header, 'first' => $first, 'last' => $last, 'cols' => $cols];
    }

    /** Biểu đồ từ một bảng: $type line|bar|pie, $labelCol & $valueCols là chữ cột (A, C...) */
    public function chart(string $type, string $title, array $table, string $labelCol, array $valueCols): static
    {
        $count = $table['last'] - $table['first'] + 1;
        if ($count < ($type === 'line' ? 2 : 1)) {
            return $this; // không đủ dữ liệu để vẽ
        }

        $ref = fn (string $col, int $from, int $to) => "'" . str_replace("'", "''", $table['sheet']) . "'!\${$col}\${$from}:\${$col}\${$to}";
        $labels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $ref($labelCol, $table['first'], $table['last']), null, $count)];

        $names = $values = [];
        foreach (array_values($valueCols) as $i => $col) {
            $names[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $ref($col, $table['header'], $table['header']), null, 1);
            $fill = $type === 'pie' ? array_slice(array_merge(self::PALETTE, self::PALETTE), 0, max($count, 1)) : self::PALETTE[$i % count(self::PALETTE)];
            $values[] = new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $ref($col, $table['first'], $table['last']), null, $count, [], $type === 'line' ? 'circle' : null, $fill);
        }

        $series = new DataSeries(
            match ($type) { 'line' => DataSeries::TYPE_LINECHART, 'pie' => DataSeries::TYPE_PIECHART, default => DataSeries::TYPE_BARCHART },
            $type === 'pie' ? null : DataSeries::GROUPING_CLUSTERED,
            range(0, count($values) - 1),
            $names, $labels, $values,
        );
        if ($type === 'bar') {
            $series->setPlotDirection(DataSeries::DIRECTION_COL);
        }

        $layout = null;
        if ($type === 'pie') {
            $layout = new Layout();
            $layout->setShowPercent(true);
        }

        $chart = new Chart('c' . uniqid(), new Title($title), new Legend($type === 'pie' ? Legend::POSITION_RIGHT : Legend::POSITION_BOTTOM, null, false), new PlotArea($layout, [$series]));
        $left = Coordinate::stringFromColumnIndex($this->width + 2);
        $right = Coordinate::stringFromColumnIndex($this->width + 10);
        $chart->setTopLeftPosition($left . $this->chartRow);
        $chart->setBottomRightPosition($right . ($this->chartRow + 17));
        $this->sheet->addChart($chart);
        $this->chartRow += 19;

        return $this;
    }

    public function download(string $filename): StreamedResponse
    {
        foreach ($this->book->getAllSheets() as $sheet) {
            // Cột A chứa dải tiêu đề dài nên không tự giãn
            $sheet->getColumnDimension('A')->setWidth(26);
            foreach (range(2, max(2, Coordinate::columnIndexFromString($sheet->getHighestDataColumn()))) as $c) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
            }
        }
        $this->book->setActiveSheetIndex(0);

        return response()->streamDownload(function () {
            $writer = new Xlsx($this->book);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
        }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Độ chia trục thời gian cho khoảng [from, to]: 1 ngày → theo giờ, ≤ 62 ngày → theo ngày, dài hơn → theo tháng */
    public static function grain(\DateTimeInterface $from, \DateTimeInterface $to): string
    {
        $days = Carbon::instance($from)->diffInDays($to);

        return $days < 1 ? 'hour' : ($days <= 62 ? 'day' : 'month');
    }

    /** Các mốc trên trục thời gian: [khoá => nhãn], khoá khớp với bucketOf() */
    public static function buckets(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $grain = self::grain($from, $to);
        if ($grain === 'hour') {
            return collect(range(0, 23))->mapWithKeys(fn ($h) => [sprintf('%02d', $h) => sprintf('%02d:00', $h)])->all();
        }
        $out = [];
        $step = $grain === 'day' ? '1 day' : '1 month';
        $start = $grain === 'day' ? Carbon::instance($from)->startOfDay() : Carbon::instance($from)->startOfMonth();
        foreach (CarbonPeriod::create($start, $step, $to) as $d) {
            $out[self::bucketOf($d, $grain)] = $d->format($grain === 'day' ? 'd/m' : 'm/Y');
        }

        return $out;
    }

    public static function bucketOf(\DateTimeInterface $t, string $grain): string
    {
        return $t->format(['hour' => 'H', 'day' => 'Y-m-d', 'month' => 'Y-m'][$grain]);
    }

    /** Chuỗi luôn ghi thành chữ (chống chèn công thức: tên khách bắt đầu bằng "=") */
    private function put(int $col, int $row, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $cell = Coordinate::stringFromColumnIndex($col) . $row;
        is_string($value)
            ? $this->sheet->setCellValueExplicit($cell, $value, DataType::TYPE_STRING)
            : $this->sheet->setCellValue($cell, $value);
    }
}
