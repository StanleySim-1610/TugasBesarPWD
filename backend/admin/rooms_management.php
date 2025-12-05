<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$error = '';
$success = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $tipe_kamar = sanitize($_POST['tipe_kamar']);
        $jumlah_tersedia = intval($_POST['jumlah_tersedia']);
        $harga = floatval($_POST['harga']);
        $deskripsi = sanitize($_POST['deskripsi']);
        
        $stmt = $conn->prepare("INSERT INTO kamar (tipe_kamar, jumlah_tersedia, harga, deskripsi) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sids", $tipe_kamar, $jumlah_tersedia, $harga, $deskripsi);
        
        if ($stmt->execute()) {
            $success = 'Room added successfully!';
        } else {
            $error = 'Failed to add room!';
        }
    } elseif ($action == 'edit') {
        $id = intval($_POST['id_kamar']);
        $tipe_kamar = sanitize($_POST['tipe_kamar']);
        $jumlah_tersedia = intval($_POST['jumlah_tersedia']);
        $harga = floatval($_POST['harga']);
        $deskripsi = sanitize($_POST['deskripsi']);
        
        $stmt = $conn->prepare("UPDATE kamar SET tipe_kamar = ?, jumlah_tersedia = ?, harga = ?, deskripsi = ? WHERE id_kamar = ?");
        $stmt->bind_param("sidsi", $tipe_kamar, $jumlah_tersedia, $harga, $deskripsi, $id);
        
        if ($stmt->execute()) {
            $success = 'Room updated successfully!';
        } else {
            $error = 'Failed to update room!';
        }
    } elseif ($action == 'delete') {
        $id = intval($_POST['id_kamar']);
        
        // Check if room has reservations
        $check = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_kamar = $id");
        if ($check->fetch_assoc()['count'] > 0) {
            $error = 'Cannot delete room with existing reservations!';
        } else {
            if ($conn->query("DELETE FROM kamar WHERE id_kamar = $id")) {
                $success = 'Room deleted successfully!';
            } else {
                $error = 'Failed to delete room!';
            }
        }
    }
}

// Get all rooms
$rooms = $conn->query("SELECT * FROM kamar ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms Management - Admin</title>
    <link rel="stylesheet" href="../../frontend/assets/css/dashboard.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .modal-close {
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .modal-close:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar admin-sidebar">
            <div class="sidebar-header">
                <img src="../assets/logo.png?v=2" alt="Logo" class="sidebar-logo">
                <h3>Admin Panel</h3>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
                <a href="users.php" class="nav-item">
                    <span class="nav-icon">👥</span>
                    Users Management
                </a>
                <a href="rooms_management.php" class="nav-item active">
                    <span class="nav-icon">🏨</span>
                    Rooms Management
                </a>
                <a href="reservations_management.php" class="nav-item">
                    <span class="nav-icon">📅</span>
                    Reservations
                </a>
                <a href="payments.php" class="nav-item">
                    <span class="nav-icon">💳</span>
                    Payments
                </a>
                <a href="fnb_orders.php" class="nav-item">
                    <span class="nav-icon">🍽️</span>
                    F&B Orders
                </a>
                <a href="../logout.php" class="nav-item">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <div class="top-bar">
                <h1>Rooms Management</h1>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add New Room</button>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <section class="content-section">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Room Type</th>
                                <th>Available</th>
                                <th>Price/Night</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($room = $rooms->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $room['id_kamar']; ?></td>
                                <td><strong><?php echo htmlspecialchars($room['tipe_kamar']); ?></strong></td>
                                <td><?php echo $room['jumlah_tersedia']; ?> rooms</td>
                                <td><?php echo formatRupiah($room['harga']); ?></td>
                                <td><?php echo htmlspecialchars(substr($room['deskripsi'], 0, 50)) . '...'; ?></td>
                                <td>
                                    <button class="btn btn-sm" onclick='editRoom(<?php echo json_encode($room); ?>)'>Edit</button>
                                    <button class="btn btn-sm" style="background: #f44336;" onclick="deleteRoom(<?php echo $room['id_kamar']; ?>)">Delete</button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Add/Edit Modal -->
    <div id="roomModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Room</h2>
                <span class="modal-close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id_kamar" id="roomId">
                
                <div class="form-group">
                    <label for="tipe_kamar">Room Type</label>
                    <input type="text" id="tipe_kamar" name="tipe_kamar" required>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="jumlah_tersedia">Available Rooms</label>
                        <input type="number" id="jumlah_tersedia" name="jumlah_tersedia" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga">Price per Night</label>
                        <input type="number" id="harga" name="harga" min="0" step="1000" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Description</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" required></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Room</button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New Room';
            document.getElementById('formAction').value = 'add';
            document.getElementById('roomId').value = '';
            document.getElementById('tipe_kamar').value = '';
            document.getElementById('jumlah_tersedia').value = '';
            document.getElementById('harga').value = '';
            document.getElementById('deskripsi').value = '';
            document.getElementById('roomModal').classList.add('active');
        }
        
        function editRoom(room) {
            document.getElementById('modalTitle').textContent = 'Edit Room';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('roomId').value = room.id_kamar;
            document.getElementById('tipe_kamar').value = room.tipe_kamar;
            document.getElementById('jumlah_tersedia').value = room.jumlah_tersedia;
            document.getElementById('harga').value = room.harga;
            document.getElementById('deskripsi').value = room.deskripsi;
            document.getElementById('roomModal').classList.add('active');
        }
        
        function deleteRoom(id) {
            if (confirm('Are you sure you want to delete this room?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_kamar" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function closeModal() {
            document.getElementById('roomModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('roomModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
