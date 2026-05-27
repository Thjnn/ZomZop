<?php

// ============================================================
// SEEDER: database/seeders/MenuItemImageSeeder.php
// Map đúng tên file thật trong public/images/products/
// ============================================================

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuItemImage;

class MenuItemImageSeeder extends Seeder
{
    public function run(): void
    {
        // slug => [ [file, alt, is_primary], ... ]
        $images = [

            // ── BURGER ───────────────────────────────────────────
            'bbq-bacon' => [
                ['file' => 'buger-bacon-1.png',   'alt' => 'BBQ Bacon Burger góc trước',  'primary' => true],
                ['file' => 'buger-bacon-2.png',   'alt' => 'BBQ Bacon Burger góc nghiêng'],
                ['file' => 'buger-co-dien-3.png', 'alt' => 'BBQ Bacon Burger cắt đôi'],
            ],
            'crispy-chicken' => [
                ['file' => 'buger-ga-crispy-1.jpg',  'alt' => 'Crispy Chicken Burger', 'primary' => true],
                ['file' => 'buger-ga-crispy-2.jpg',  'alt' => 'Crispy Chicken Burger nhìn gần'],
                ['file' => 'buger-ga-crispy-3.png',  'alt' => 'Crispy Chicken Burger cắt đôi'],
            ],
            'spicy-chicken' => [
                ['file' => 'buger-ga-spicy-1.jpeg', 'alt' => 'Spicy Chicken Burger',        'primary' => true],
                ['file' => 'buger-ga-spicy-2.jpg',  'alt' => 'Spicy Chicken Burger góc 2'],
                ['file' => 'buger-ga-spicy-3.png',  'alt' => 'Spicy Chicken Burger cắt đôi'],
            ],
            'classic-burger' => [
                ['file' => 'buger-nam-1.jpg',   'alt' => 'Classic Burger',             'primary' => true],
                ['file' => 'buger-nam-2.jpg',   'alt' => 'Classic Burger góc nghiêng'],
                ['file' => 'buger-nam-3.webp',  'alt' => 'Classic Burger cắt đôi'],
            ],

            // ── PIZZA ────────────────────────────────────────────
            'pizza-pho-mai' => [
                ['file' => 'cheese-pizza-3.jpg',   'alt' => 'Pizza Phô Mai',           'primary' => true],
                ['file' => 'pizza-phomai-1.jpg',   'alt' => 'Pizza Phô Mai góc trên'],
                ['file' => 'pizza-phomai-2.jpg',   'alt' => 'Pizza Phô Mai miếng cắt'],
            ],
            'pizza-bbq-ga' => [
                ['file' => 'pizza-bbq-ga-1.png',   'alt' => 'Pizza BBQ Gà',            'primary' => true],
                ['file' => 'pizza-bbq-ga-2.png',   'alt' => 'Pizza BBQ Gà góc nghiêng'],
            ],
            'pizza-cay-kieu-y' => [
                ['file' => 'pizza-cay-kieuY-1.jpg',  'alt' => 'Pizza Cay Kiểu Ý',      'primary' => true],
                ['file' => 'pizza-cay-kieuY-2.webp', 'alt' => 'Pizza Cay Kiểu Ý góc 2'],
            ],
            'pizza-hai-san' => [
                ['file' => 'pizza-haisan-1.jpg',   'alt' => 'Pizza Hải Sản',            'primary' => true],
                ['file' => 'pizza-haisan-2.jpg',   'alt' => 'Pizza Hải Sản góc trên'],
                ['file' => 'pizza-haisan-3.jpg',   'alt' => 'Pizza Hải Sản miếng cắt'],
            ],

            // ── MỲ Ý ─────────────────────────────────────────────
            'spaghetti-bo-bam' => [
                ['file' => 'mi-bo-bam-1.png',   'alt' => 'Spaghetti Bò Bằm',           'primary' => true],
                ['file' => 'mi-bo-bam-2.webp',  'alt' => 'Spaghetti Bò Bằm góc gần'],
            ],
            'spaghetti-kem-ga' => [
                ['file' => 'mi-kem-ga-1.png',   'alt' => 'Spaghetti Kem Gà',            'primary' => true],
                ['file' => 'mi-kem-ga-2.png',   'alt' => 'Spaghetti Kem Gà góc nghiêng'],
                ['file' => 'mi-kem-ga-3.png',   'alt' => 'Spaghetti Kem Gà đặc cận'],
            ],
            'penne-ca-chua' => [
                ['file' => 'penne-cachua-1.jpg',  'alt' => 'Penne Cà Chua',             'primary' => true],
                ['file' => 'penne-cachua-2.jpg',  'alt' => 'Penne Cà Chua góc nghiêng'],
                ['file' => 'penne-cachua-3.jpg',  'alt' => 'Penne Cà Chua cận sốt'],
            ],

            // ── SANDWICH ─────────────────────────────────────────
            'sandwich-blt-bacon' => [
                ['file' => 'sandwick-blt-1.jpg',   'alt' => 'Sandwich BLT Bacon',        'primary' => true],
                ['file' => 'sandwick-blt-2.jpg',   'alt' => 'Sandwich BLT Bacon góc 2'],
                ['file' => 'sandwick-blt-3.jpg',   'alt' => 'Sandwich BLT Bacon cắt đôi'],
            ],
            'sandwich-ga-nuong' => [
                ['file' => 'sandwick-ga-2.webp',  'alt' => 'Sandwich Gà Nướng',          'primary' => true],
                ['file' => 'sandwick-ga-3.jpg',   'alt' => 'Sandwich Gà Nướng góc 2'],
            ],
            'sandwich-trung-pho-mai' => [
                ['file' => 'sandwick-trung-phomai-1.jpg', 'alt' => 'Sandwich Trứng Phô Mai',     'primary' => true],
                ['file' => 'sandwick-trung-phomai-2.jpg', 'alt' => 'Sandwich Trứng Phô Mai góc 2'],
            ],

            // ── GÀ CHIÊN ─────────────────────────────────────────
            'ga-chien-gion-2' => [
                ['file' => 'ga-chien.webp',   'alt' => 'Gà Chiên Giòn 2 miếng',         'primary' => true],
            ],
            'ga-chien-gion-4' => [
                ['file' => 'ga-chien-2.webp', 'alt' => 'Gà Chiên Giòn 4 miếng',         'primary' => true],
            ],
            'ga-chien-cay-2' => [
                ['file' => 'ga-chien.webp',   'alt' => 'Gà Chiên Cay 2 miếng',           'primary' => true],
            ],
            'ga-chien-cay-4' => [
                ['file' => 'ga-chien-2.webp', 'alt' => 'Gà Chiên Cay 4 miếng',           'primary' => true],
            ],

            // ── SIDES ─────────────────────────────────────────────
            'coleslaw' => [
                ['file' => 'coleslaw.jpg',   'alt' => 'Coleslaw salad',                  'primary' => true],
            ],
            'nuggets-6' => [
                ['file' => 'nuggets.jpg',    'alt' => 'Nuggets Gà 6 miếng',              'primary' => true],
            ],
            'nuggets-12' => [
                ['file' => 'nuggets.jpg',    'alt' => 'Nuggets Gà 12 miếng',             'primary' => true],
            ],

            // ── COMBO ─────────────────────────────────────────────
            'combo-co-ban' => [
                ['file' => 'combo-1.jpg',    'alt' => 'Combo Cơ Bản',                    'primary' => true],
            ],
            'combo-doi' => [
                ['file' => 'combo-2.jpg',    'alt' => 'Combo Đôi',                       'primary' => true],
            ],

            // ── ĐỒ UỐNG ───────────────────────────────────────────
            'nuoc-ngot' => [
                ['file' => 'nuoc-ngot.jpg',  'alt' => 'Nước Ngọt',                       'primary' => true],
            ],
            'nuoc-suoi' => [
                ['file' => 'nuoc-suoi.jpg',  'alt' => 'Nước Suối',                       'primary' => true],
            ],
        ];

        foreach ($images as $slug => $imgs) {
            $menuItem = MenuItem::where('slug', $slug)->first();
            if (!$menuItem) continue;

            foreach ($imgs as $index => $img) {
                MenuItemImage::create([
                    'menu_item_id' => $menuItem->id,
                    'image'        => $img['file'],
                    'alt_text'     => $img['alt'],
                    'sort_order'   => $index + 1,
                    'is_primary'   => $img['primary'] ?? false,
                ]);
            }
        }
    }
}
