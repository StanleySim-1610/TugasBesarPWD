<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Menu F&B dengan harga - Chinese Cuisine Theme
$menu_items = [
    [
        'id' => 1,
        'kategori' => 'Makanan',
        'nama' => 'Asam Manis',
        'harga' => 55000,
        'deskripsi' => 'Ayam goreng dengan saus asam manis khas Chinese',
        'foto' => 'asam_manis.jpg'
    ],
    [
        'id' => 2,
        'kategori' => 'Makanan',
        'nama' => 'Bakpao Nonhalal',
        'harga' => 32000,
        'deskripsi' => 'Bakpao kukus isi daging babi cincang berbumbu',
        'foto' => 'bakpao_nonhalal.jpg'
    ],
    [
        'id' => 3,
        'kategori' => 'Makanan',
        'nama' => 'Bihun Goreng Seafood',
        'harga' => 48000,
        'deskripsi' => 'Bihun goreng dengan udang, cumi, dan sayuran segar',
        'foto' => 'bihun_goreng_seafood.jpeg'
    ],
    [
        'id' => 4,
        'kategori' => 'Makanan',
        'nama' => 'Kwetiau Seafood',
        'harga' => 52000,
        'deskripsi' => 'Kwetiau goreng seafood dengan udang, cumi, dan sayuran',
        'foto' => 'kwetiau_seafood.jpg'
    ],
    [
        'id' => 5,
        'kategori' => 'Makanan',
        'nama' => 'Mie Pangsit Ayam',
        'harga' => 45000,
        'deskripsi' => 'Mie dengan pangsit ayam, sayuran dalam kuah gurih',
        'foto' => 'mie_pangsit_ayam.jpeg'
    ],
    [
        'id' => 6,
        'kategori' => 'Makanan',
        'nama' => 'Mie Sagu Goreng Teri',
        'harga' => 42000,
        'deskripsi' => 'Mie sagu goreng dengan teri crispy dan sayuran',
        'foto' => 'mie_sagu_goreng_teri.png'
    ],
    [
        'id' => 7,
        'kategori' => 'Makanan',
        'nama' => 'Misoa Ayam',
        'harga' => 40000,
        'deskripsi' => 'Misoa kuah dengan ayam dan sayuran segar',
        'foto' => 'misoa_ayam.jpg'
    ],
    [
        'id' => 8,
        'kategori' => 'Makanan',
        'nama' => 'Moon Cake',
        'harga' => 35000,
        'deskripsi' => 'Kue bulan tradisional dengan isian lotus seed paste',
        'foto' => 'moon_cake.jpg'
    ],
    [
        'id' => 9,
        'kategori' => 'Makanan',
        'nama' => 'Nasi Kungpao',
        'harga' => 58000,
        'deskripsi' => 'Nasi dengan ayam kungpao pedas dan kacang mete',
        'foto' => 'nasi_kungpao.jpg'
    ],
    [
        'id' => 10,
        'kategori' => 'Minuman',
        'nama' => 'Ai Yu Jelly',
        'harga' => 22000,
        'deskripsi' => 'Minuman jelly ai yu dengan lemon segar',
        'foto' => 'ai_yu_jelly.jpg'
    ],
    [
        'id' => 11,
        'kategori' => 'Minuman',
        'nama' => 'Ba Bao Cha',
        'harga' => 25000,
        'deskripsi' => 'Teh delapan harta karun dengan buah kering',
        'foto' => 'ba_bao_cha.jpg'
    ],
    [
        'id' => 12,
        'kategori' => 'Minuman',
        'nama' => 'Chinese Tea',
        'harga' => 20000,
        'deskripsi' => 'Teh tradisional China pilihan',
        'foto' => 'chinese_tea.jpg'
    ],
    [
        'id' => 13,
        'kategori' => 'Minuman',
        'nama' => 'Es Lidah Buaya',
        'harga' => 20000,
        'deskripsi' => 'Minuman lidah buaya dengan es yang menyegarkan',
        'foto' => 'es_lidah_buaya.png'
    ],
    [
        'id' => 14,
        'kategori' => 'Minuman',
        'nama' => 'Kha Hue Teh',
        'harga' => 18000,
        'deskripsi' => 'Teh bunga krisan yang menyegarkan',
        'foto' => 'kha_hue_teh.jpg'
    ],
    [
        'id' => 15,
        'kategori' => 'Minuman',
        'nama' => 'Lo Han Guo',
        'harga' => 20000,
        'deskripsi' => 'Teh buah monk yang manis alami dan menyehatkan',
        'foto' => 'lo_han_guo.jpg'
    ]
];

echo json_encode([
    'success' => true,
    'data' => $menu_items
]);
?>
