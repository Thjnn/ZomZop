<?php

namespace App\Http\Controllers\Manager;

use App\Models\User;
use App\Support\ExcelImport;
use App\Support\ExcelReport;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends ManagerController
{
    public const ROLES = ['staff' => 'Nhân viên', 'kitchen' => 'Bếp'];

    private const MESSAGES = [
        'name.required'      => 'Vui lòng nhập họ tên.',
        'email.required'     => 'Vui lòng nhập email.',
        'email.email'        => 'Email không hợp lệ.',
        'email.unique'       => 'Email này đã có người sử dụng.',
        'phone.regex'        => 'Số điện thoại không hợp lệ (VD: 0901234567).',
        'role.in'            => 'Vai trò chỉ được là Nhân viên hoặc Bếp.',
        'password.required'  => 'Vui lòng nhập mật khẩu.',
        'password.min'       => 'Mật khẩu tối thiểu 8 ký tự.',
        'password.confirmed' => 'Nhập lại mật khẩu không khớp.',
        'started_at.required'        => 'Vui lòng nhập ngày bắt đầu làm.',
        'started_at.date_format'     => 'Ngày bắt đầu không hợp lệ.',
        'started_at.before_or_equal' => 'Ngày bắt đầu không được quá 30 ngày tới.',
        'probation_rate.*'           => 'Lương thử việc/giờ phải là số nguyên từ 1 đến 999.999.999.',
        'salary_rate.*'              => 'Lương chính thức/giờ phải là số nguyên từ 1 đến 999.999.999.',
    ];

    private const IMPORT_COLUMNS = [
        'name'           => 'Họ tên',
        'email'          => 'Email',
        'phone'          => 'SĐT',
        'role'           => 'Vai trò',
        'started_at'     => 'Ngày bắt đầu',
        'probation_rate' => 'Lương thử việc/giờ',
        'salary_rate'    => 'Lương chính thức/giờ',
        'password'       => 'Mật khẩu',
    ];

    private const USER_FIELDS = ['name', 'email', 'phone', 'role', 'started_at'];

    private function rules(?User $user = null): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'regex:/^(0|\+84)[0-9]{9,10}$/'],
            'role'  => ['required', Rule::in(array_keys(self::ROLES))],
            'started_at'     => [$user ? 'nullable' : 'required', 'date_format:Y-m-d', 'before_or_equal:' . today()->addDays(30)->toDateString()],
            'probation_rate' => ['required', 'integer', 'min:1', 'max:999999999'],
            'salary_rate'    => ['required', 'integer', 'min:1', 'max:999999999'],
        ];
    }

    public function index()
    {
        $staff = User::where('branch_id', $this->branchId())
            ->whereIn('role', array_keys(self::ROLES))
            ->with('latestSalary')->withCount('faceDescriptors')
            ->orderByDesc('is_active')->orderBy('name')
            ->get();

        return view('manager.staff.index', ['staff' => $staff, 'roles' => self::ROLES]);
    }

    public function create()
    {
        return view('manager.staff.form', ['user' => null, 'roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules() + [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], self::MESSAGES);

        // branch_id, is_active do server quyết định — bỏ qua mọi giá trị client gửi lên
        $user = User::create(Arr::only($data, [...self::USER_FIELDS, 'password']) + ['branch_id' => $this->branchId(), 'is_active' => true]);
        $this->saveSalary($user, $data);

        return redirect()->route('manager.staff.index')->with('success', "Đã tạo tài khoản cho {$data['name']}.");
    }

    public function edit(User $user)
    {
        $this->ensureOwnStaff($user);
        $user->load('latestSalary');

        return view('manager.staff.form', ['user' => $user, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $user)
    {
        $this->ensureOwnStaff($user);

        $data = $request->validate($this->rules($user), self::MESSAGES);
        $user->update(Arr::only($data, self::USER_FIELDS));
        $this->saveSalary($user, $data);

        return redirect()->route('manager.staff.index')->with('success', "Đã cập nhật {$user->name}.");
    }

    /** Đổi lương thì lưu mức mới hiệu lực từ hôm nay; mức đầu tiên tính từ ngày bắt đầu làm. Giữ nguyên lịch sử cũ */
    private function saveSalary(User $user, array $data): void
    {
        $current = $user->latestSalary()->first();

        if ($current && (int) $current->rate === (int) $data['salary_rate']
            && (int) $current->probation_rate === (int) $data['probation_rate']) {
            return;
        }

        $user->salaryConfigs()->create([
            'type'           => 'hourly',
            'rate'           => $data['salary_rate'],
            'probation_rate' => $data['probation_rate'],
            'effective_from' => $current ? today() : min(today(), $user->started_at ?? today()),
        ]);
    }

    public function toggleLock(User $user)
    {
        $this->ensureOwnStaff($user);

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', $user->is_active ? "Đã mở khoá {$user->name}." : "Đã khoá {$user->name}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureOwnStaff($user);

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']], self::MESSAGES);
        $user->update(['password' => $data['password']]); // cast 'hashed' trong User tự mã hoá

        return back()->with('success', "Đã đặt lại mật khẩu cho {$user->name}.");
    }

    public function importTemplate()
    {
        $report = new ExcelReport('Mẫu nhập nhân viên — ' . $this->branchName(), 'Điền từ dòng 5 · Vai trò: Nhân viên hoặc Bếp · Ngày dạng 12/11/2026 (trống = hôm nay) · Mật khẩu ≥ 8 ký tự, xoá file sau khi nhập');
        $report->sheet('Nhân viên')->table(array_values(self::IMPORT_COLUMNS), []);

        return $report->download('mau-nhap-nhan-vien.xlsx');
    }

    /** Chỉ tạo tài khoản mới cho chi nhánh mình; khuôn mặt vẫn đăng ký tay ở từng nhân viên */
    public function import(Request $request)
    {
        $request->validate(['file' => ExcelImport::FILE_RULES], ExcelImport::FILE_MESSAGES);
        $rows  = ExcelImport::rows($request->file('file'), self::IMPORT_COLUMNS);
        $roles = ['nhân viên' => 'staff', 'staff' => 'staff', 'bếp' => 'kitchen', 'kitchen' => 'kitchen'];
        $rules = $this->rules() + ['password' => ['required', 'string', 'min:8']];

        $errors = $people = $seen = [];
        foreach ($rows as $line => $row) {
            $phone = $row['phone'];
            if ($phone !== null && ctype_digit($phone) && $phone[0] !== '0') {
                $phone = '0' . $phone; // Excel lưu SĐT dạng số nên rơi mất số 0 đầu
            }
            $data = [
                'role'           => $roles[mb_strtolower((string) $row['role'])] ?? $row['role'],
                'phone'          => $phone,
                'started_at'     => ExcelImport::date($row['started_at']) ?? today()->toDateString(),
                'probation_rate' => ExcelImport::int($row['probation_rate']),
                'salary_rate'    => ExcelImport::int($row['salary_rate']),
            ] + $row;

            $rowErrors = Validator::make($data, $rules, self::MESSAGES)->errors()->all();
            $key = mb_strtolower((string) $row['email']);
            if ($key !== '' && isset($seen[$key])) {
                $rowErrors[] = "Email trùng với dòng {$seen[$key]}.";
            }
            $seen[$key] ??= $line;

            foreach ($rowErrors as $msg) {
                $errors[] = "Dòng {$line}: {$msg}";
            }
            $people[] = $data;
        }
        if ($errors) {
            ExcelImport::fail($errors);
        }

        DB::transaction(function () use ($people) {
            foreach ($people as $data) {
                $user = User::create(Arr::only($data, [...self::USER_FIELDS, 'password']) + ['branch_id' => $this->branchId(), 'is_active' => true]);
                $this->saveSalary($user, $data);
            }
        });

        return redirect()->route('manager.staff.index')->with('success', 'Đã tạo ' . count($people) . ' tài khoản từ Excel. Nhớ đăng ký khuôn mặt cho từng người.');
    }
}
