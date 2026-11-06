<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\User;
use App\Services\BranchMenu;

/**
 * Giỏ hàng vẫn nằm trong session (như cũ), nhưng được chép vào cột users.cart
 * để đăng xuất / hết phiên rồi đăng nhập lại không mất món.
 */
class SavedCart
{
    /** Chép giỏ trong session vào tài khoản; chỉ ghi DB khi có thay đổi */
    public static function store(User $user): void
    {
        $cart = session('cart');
        $cart = !empty($cart['items']) ? $cart : null;

        if ($user->cart != $cart) {
            $user->forceFill(['cart' => $cart])->save();
        }
    }

    /** Lúc đăng nhập: lấy lại giỏ đã lưu, gộp với giỏ khách vừa thêm lúc chưa đăng nhập */
    public static function restore(User $user): void
    {
        $saved = $user->cart;
        if (empty($saved['items']) || empty($saved['branch_id'])) {
            return;
        }

        $branch = Branch::whereKey($saved['branch_id'])->where('is_active', true)->first();
        if (!$branch) {
            return; // chi nhánh đã đóng: bỏ giỏ cũ, lần đồng bộ sau sẽ ghi đè
        }

        $current = session('cart');
        if (empty($current['items'])) {
            $cart = $saved;
        } elseif ((int) $current['branch_id'] === (int) $saved['branch_id']) {
            $cart = self::merge($current, $saved);
        } else {
            // ponytail: khác chi nhánh thì giữ giỏ mới nhất (giỏ trong session), như khi đổi chi nhánh
            return;
        }

        $cart['branch_id'] = $branch->id;
        session(['cart' => $cart]);

        // Thêm món dùng selected_branch_id: thiếu hoặc lệch thì giỏ bị reset ở lần thêm kế tiếp
        if (!session('selected_branch_id')) {
            session(['selected_branch_id' => $branch->id, 'selected_branch_name' => $branch->name]);
        }
    }

    private static function merge(array $current, array $saved): array
    {
        foreach ($saved['items'] as $line) {
            $key = BranchMenu::lineKey($line);
            foreach ($current['items'] as $i => $ci) {
                if (BranchMenu::lineKey($ci) === $key) {
                    $current['items'][$i]['quantity'] = min(99, $ci['quantity'] + $line['quantity']);
                    continue 2;
                }
            }
            $current['items'][] = $line;
        }

        return $current;
    }
}
