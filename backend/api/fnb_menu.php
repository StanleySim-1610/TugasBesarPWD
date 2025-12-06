<?php
require_once '../config/database.php';
header('Content-Type: application/json');

// Menu F&B dengan harga
$menu_items = [
    [
        'id' => 1,
        'kategori' => 'Makanan',
        'nama' => 'Nasi Goreng Spesial',
        'harga' => 45000,
        'deskripsi' => 'Nasi goreng dengan telur, ayam, dan sayuran',
        'foto' => 'nasi_goreng.jpg'
    ],
    [
        'id' => 2,
        'kategori' => 'Makanan',
        'nama' => 'Mie Goreng',
        'harga' => 40000,
        'deskripsi' => 'Mie goreng dengan sayuran segar',
        'foto' => 'mie_goreng.jpg'
    ],
    [
        'id' => 3,
        'kategori' => 'Makanan',
        'nama' => 'Ayam Bakar',
        'harga' => 55000,
        'deskripsi' => 'Ayam bakar dengan bumbu khas dan nasi',
        'foto' => 'ayam_bakar.jpg'
    ],
    [
        'id' => 4,
        'kategori' => 'Makanan',
        'nama' => 'Ikan Bakar',
        'harga' => 65000,
        'deskripsi' => 'Ikan bakar segar dengan sambal',
        'foto' => 'ikan_bakar.jpg'
    ],
    [
        'id' => 5,
        'kategori' => 'Makanan',
        'nama' => 'Capcay',
        'harga' => 38000,
        'deskripsi' => 'Tumis sayuran campur ala China',
        'foto' => 'capcay.jpg'
    ],
    [
        'id' => 6,
        'kategori' => 'Makanan',
        'nama' => 'Sate Ayam',
        'harga' => 50000,
        'deskripsi' => '10 tusuk sate ayam dengan bumbu kacang',
        'foto' => 'sate_ayam.jpg'
    ],
    [
        'id' => 7,
        'kategori' => 'Minuman',
        'nama' => 'Es Teh Manis',
        'harga' => 10000,
        'deskripsi' => 'Teh manis dingin segar',
        'foto' => 'es_teh.jpg'
    ],
    [
        'id' => 8,
        'kategori' => 'Minuman',
        'nama' => 'Es Jeruk',
        'harga' => 12000,
        'deskripsi' => 'Jus jeruk segar',
        'foto' => 'es_jeruk.jpg'
    ],
    [
        'id' => 9,
        'kategori' => 'Minuman',
        'nama' => 'Kopi Hitam',
        'harga' => 15000,
        'deskripsi' => 'Kopi hitam murni',
        'foto' => 'kopi.jpg'
    ],
    [
        'id' => 10,
        'kategori' => 'Minuman',
        'nama' => 'Cappuccino',
        'harga' => 25000,
        'deskripsi' => 'Kopi susu dengan foam',
        'foto' => 'cappuccino.jpg'
    ],
    [
        'id' => 11,
        'kategori' => 'Minuman',
        'nama' => 'Jus Alpukat',
        'harga' => 20000,
        'deskripsi' => 'Jus alpukat segar',
        'foto' => 'jus_alpukat.jpg'
    ],
    [
        'id' => 12,
        'kategori' => 'Minuman',
        'nama' => 'Es Campur',
        'harga' => 18000,
        'deskripsi' => 'Minuman es campur dengan buah',
        'foto' => 'es_campur.jpg'
    ],
    [
        'id' => 13,
        'kategori' => 'Snack',
        'nama' => 'French Fries',
        'harga' => 25000,
        'deskripsi' => 'Kentang goreng crispy',
        'foto' => 'french_fries.jpg'
    ],
    [
        'id' => 14,
        'kategori' => 'Snack',
        'nama' => 'Chicken Wings',
        'harga' => 35000,
        'deskripsi' => 'Sayap ayam goreng pedas',
        'foto' => 'wings.jpg'
    ],
    [
        'id' => 15,
        'kategori' => 'Snack',
        'nama' => 'Spring Roll',
        'harga' => 30000,
        'deskripsi' => 'Lumpia goreng isi sayuran',
        'foto' => 'spring_roll.jpg'
    ]
];

echo json_encode([
    'success' => true,
    'data' => $menu_items
]);
?>
