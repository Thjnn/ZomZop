<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;

/**
 * Đọc file .xlsx/.csv người dùng nhập vào. File mẫu tải về dựng bằng ExcelReport (tiêu đề bảng ở dòng 4),
 * nên dòng tiêu đề được dò theo tên cột chứ không cố định ở dòng 1.
 *   ExcelImport::rows($file, ['name' => 'Tên món', ...]) → [số dòng Excel => ['name' => '...', ...]]
 * Lỗi gom bằng ExcelImport::fail([...]) → báo "Dòng N: ..." trên khoá 'file', không lưu gì.
 */
class ExcelImport
{
    public const MAX_ROWS = 500; // ponytail: đọc cả file vào RAM, file lớn hơn thì chuyển sang đọc theo khối

    public const FILE_RULES = ['required', 'file', 'max:2048', 'extensions:xlsx,csv'];
    public const FILE_MESSAGES = [
        'file.required'   => 'Vui lòng chọn file Excel.',
        'file.max'        => 'File tối đa 2MB.',
        'file.extensions' => 'Chỉ nhận file .xlsx hoặc .csv.',
    ];

    public static function rows(UploadedFile $file, array $headings): array
    {
        try {
            $csv = strtolower($file->getClientOriginalExtension()) === 'csv';
            $reader = IOFactory::createReader($csv ? 'Csv' : 'Xlsx');
            $reader->setReadDataOnly(true);
            if ($csv) {
                $reader->setValueBinder(new StringValueBinder()); // giữ nguyên chữ, không để "45.000" thành 45
            }
            $grid = $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, false);
        } catch (Throwable) {
            self::fail(['Không đọc được file. Hãy dùng đúng file mẫu (.xlsx).']);
        }

        $norm = fn ($v) => mb_strtolower(trim((string) $v));
        $want = array_map($norm, $headings);

        // Dòng tiêu đề = dòng đầu tiên chứa cột đầu của mẫu
        foreach ($grid as $i => $cells) {
            $map = array_flip(array_filter(array_map($norm, $cells), fn ($v) => $v !== ''));
            if (isset($map[reset($want)])) {
                $missing = array_diff($want, array_keys($map));
                if ($missing) {
                    self::fail(['File thiếu cột: ' . implode(', ', array_intersect_key($headings, $missing)) . '. Hãy tải lại file mẫu.']);
                }

                $rows = [];
                foreach (array_slice($grid, $i + 1, null, true) as $j => $line) {
                    $row = [];
                    foreach ($want as $key => $h) {
                        $v = $line[$map[$h]] ?? null;
                        $row[$key] = $v === null || trim((string) $v) === '' ? null : trim((string) $v);
                    }
                    if (array_filter($row, fn ($v) => $v !== null)) {
                        $rows[$j + 1] = $row; // số dòng như Excel hiển thị
                    }
                }

                if (!$rows) {
                    self::fail(['File chưa có dòng dữ liệu nào.']);
                }
                if (count($rows) > self::MAX_ROWS) {
                    self::fail(['Mỗi lần nhập tối đa ' . self::MAX_ROWS . ' dòng.']);
                }

                return $rows;
            }
        }

        self::fail(['Không thấy dòng tiêu đề (cột "' . reset($headings) . '"). Hãy dùng đúng file mẫu.']);
    }

    public static function fail(array $messages): never
    {
        throw ValidationException::withMessages(['file' => array_slice($messages, 0, 20)]);
    }

    /** "45.000", "45,000 ₫" → "45000"; chữ lạ giữ nguyên để validator báo lỗi */
    public static function int(?string $v): ?string
    {
        return $v === null ? null : str_replace([' ', '.', ',', '₫', 'đ', 'Đ'], '', $v);
    }

    /** 1/0, có/không, x → bool; ô trống → $default; giá trị lạ → null (người gọi báo lỗi) */
    public static function bool(?string $v, bool $default): ?bool
    {
        if ($v === null) {
            return $default;
        }

        return match (mb_strtolower($v)) {
            '1', 'x', 'có', 'co', 'yes', 'true' => true,
            '0', 'không', 'khong', 'no', 'false' => false,
            default => null,
        };
    }

    /** Ô ngày của Excel là số → 'Y-m-d'; chữ dạng 12/11/2026 → '2026-11-12'; còn lại giữ nguyên */
    public static function date(?string $v): ?string
    {
        if ($v !== null && is_numeric($v)) {
            return Date::excelToDateTimeObject((float) $v)->format('Y-m-d');
        }
        if ($v !== null && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $v, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        return $v;
    }
}
