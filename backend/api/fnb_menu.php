<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Menu F&B dengan harga - Chinese Cuisine Theme
$menu_items = [
    [
        'id' => 1,
        'kategori' => 'Makanan',
        'nama' => 'Pad Thai Udang',
        'harga' => 55000,
        'deskripsi' => 'Mie beras goreng dengan udang segar, kacang, dan telur',
        'foto' => 'pad_thai.jpg'
    ],
    [
        'id' => 2,
        'kategori' => 'Makanan',
        'nama' => 'Mie Goreng Singapore',
        'harga' => 48000,
        'deskripsi' => 'Mie goreng Singapore dengan udang dan sayuran segar',
        'foto' => 'singapore_noodles.jpg'
    ],
    [
        'id' => 3,
        'kategori' => 'Makanan',
        'nama' => 'Mie Goreng Telur',
        'harga' => 42000,
        'deskripsi' => 'Mie goreng dengan telur mata sapi dan bawang goreng',
        'foto' => 'mie_goreng_telur.jpg'
    ],
    [
        'id' => 4,
        'kategori' => 'Makanan',
        'nama' => 'Laksa Pedas',
        'harga' => 52000,
        'deskripsi' => 'Mie kuah pedas dengan santan, telur, dan sayuran',
        'foto' => 'laksa.jpg'
    ],
    [
        'id' => 5,
        'kategori' => 'Makanan',
        'nama' => 'Mie Bakso Daging',
        'harga' => 45000,
        'deskripsi' => 'Mie dengan bakso daging, pangsit, dan sayuran',
        'foto' => 'mie_bakso.jpg'
    ],
    [
        'id' => 6,
        'kategori' => 'Makanan',
        'nama' => 'Kung Pao Chicken',
        'harga' => 58000,
        'deskripsi' => 'Ayam tumis pedas dengan kacang mete dan paprika',
        'foto' => 'kung_pao.jpg'
    ],
    [
        'id' => 7,
        'kategori' => 'Makanan',
        'nama' => 'Sweet and Sour Chicken',
        'harga' => 55000,
        'deskripsi' => 'Ayam goreng saus asam manis dengan nanas',
        'foto' => 'sweet_sour.jpg'
    ],
    [
        'id' => 8,
        'kategori' => 'Minuman',
        'nama' => 'Chrysanthemum Tea',
        'harga' => 18000,
        'deskripsi' => 'Teh bunga krisan tradisional China yang menyegarkan',
        'foto' => 'chrysanthemum_tea.jpg'
    ],
    [
        'id' => 9,
        'kategori' => 'Minuman',
        'nama' => 'Monk Fruit Tea',
        'harga' => 20000,
        'deskripsi' => 'Teh buah monk yang manis alami dan menyehatkan',
        'foto' => 'monk_fruit_tea.jpg'
    ],
    [
        'id' => 10,
        'kategori' => 'Minuman',
        'nama' => 'Pu-erh Tea',
        'harga' => 25000,
        'deskripsi' => 'Teh fermentasi premium khas Yunnan',
        'foto' => 'puerh_tea.jpg'
    ],
    [
        'id' => 11,
        'kategori' => 'Minuman',
        'nama' => 'Lemon Basil Seed Drink',
        'harga' => 22000,
        'deskripsi' => 'Minuman biji selasih dengan lemon segar',
        'foto' => 'lemon_basil.jpg'
    ],
    [
        'id' => 12,
        'kategori' => 'Minuman',
        'nama' => 'Aloe Vera Drink',
        'harga' => 20000,
        'deskripsi' => 'Minuman lidah buaya dengan biji selasih',
        'foto' => 'aloe_vera.jpg'
    ],
    [
        'id' => 13,
        'kategori' => 'Snack',
        'nama' => 'Mooncake',
        'harga' => 35000,
        'deskripsi' => 'Kue bulan tradisional dengan isian lotus seed paste',
        'foto' => 'mooncake.jpg'
    ],
    [
        'id' => 14,
        'kategori' => 'Snack',
        'nama' => 'Baozi (Steamed Buns)',
        'harga' => 32000,
        'deskripsi' => 'Bakpao kukus isi daging cincang berbumbu',
        'foto' => 'baozi.jpg'
    ],
    [
        'id' => 15,
        'kategori' => 'Dessert',
        'nama' => 'Chinese Herbal Soup',
        'harga' => 38000,
        'deskripsi' => 'Sup herbal manis dengan kurma, goji berry, dan longan',
        'foto' => 'herbal_soup.jpg'
    ]
];

echo json_encode([
    'success' => true,
    'data' => $menu_items
]);
?>
