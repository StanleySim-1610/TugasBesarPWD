# 🔌 API Documentation

## Database Functions & Utilities

### Authentication Functions (`config/functions.php`)

#### isLoggedIn()
```php
function isLoggedIn(): bool
```
Checks if user is currently logged in.

**Returns:** `true` if user session exists, `false` otherwise

**Example:**
```php
if (isLoggedIn()) {
    // User is logged in
}
```

---

#### isAdmin()
```php
function isAdmin(): bool
```
Checks if current user is an administrator.

**Returns:** `true` if user email is admin@gmail.com, `false` otherwise

---

#### requireLogin()
```php
function requireLogin(): void
```
Redirects to login page if user is not logged in.

**Usage:** Place at the top of protected pages

```php
requireLogin(); // Will redirect if not logged in
```

---

#### requireAdmin()
```php
function requireAdmin(): void
```
Redirects to user dashboard if not admin, to login if not logged in.

---

#### sanitize()
```php
function sanitize(string $data): string
```
Sanitizes input data to prevent XSS attacks.

**Parameters:**
- `$data` - String to sanitize

**Returns:** Sanitized string

**Example:**
```php
$clean_name = sanitize($_POST['name']);
```

---

#### validateEmail()
```php
function validateEmail(string $email): bool
```
Validates email format.

**Parameters:**
- `$email` - Email address to validate

**Returns:** `true` if valid, `false` otherwise

---

#### hashPassword()
```php
function hashPassword(string $password): string
```
Hashes password using bcrypt.

**Parameters:**
- `$password` - Plain text password

**Returns:** Hashed password

**Example:**
```php
$hashed = hashPassword('mypassword123');
// Store $hashed in database
```

---

#### verifyPassword()
```php
function verifyPassword(string $password, string $hash): bool
```
Verifies password against hash.

**Parameters:**
- `$password` - Plain text password
- `$hash` - Hashed password from database

**Returns:** `true` if match, `false` otherwise

---

#### formatRupiah()
```php
function formatRupiah(float $amount): string
```
Formats number to Indonesian Rupiah format.

**Parameters:**
- `$amount` - Numeric amount

**Returns:** Formatted string (e.g., "Rp 1.000.000")

**Example:**
```php
echo formatRupiah(500000); // Output: Rp 500.000
```

---

#### calculateDays()
```php
function calculateDays(string $checkIn, string $checkOut): int
```
Calculates number of days between two dates.

**Parameters:**
- `$checkIn` - Check-in date (Y-m-d format)
- `$checkOut` - Check-out date (Y-m-d format)

**Returns:** Number of days

**Example:**
```php
$days = calculateDays('2025-12-01', '2025-12-05'); // Returns: 4
```

---

## Database Schema

### Table: user
```sql
id_user INT PRIMARY KEY AUTO_INCREMENT
nama VARCHAR(150) NOT NULL
email VARCHAR(150) UNIQUE NOT NULL
password VARCHAR(255) NOT NULL
no_telp VARCHAR(30)
no_identitas VARCHAR(50)
foto_profil VARCHAR(255)
created_at DATETIME
updated_at DATETIME
```

### Table: kamar
```sql
id_kamar INT PRIMARY KEY AUTO_INCREMENT
tipe_kamar VARCHAR(100) NOT NULL
jumlah_tersedia INT NOT NULL
harga DECIMAL(10,2) NOT NULL
deskripsi TEXT
foto_kamar VARCHAR(255)
created_at DATETIME
updated_at DATETIME
```

### Table: reservation
```sql
id_reservation INT PRIMARY KEY AUTO_INCREMENT
id_user INT FOREIGN KEY (user.id_user)
id_kamar INT FOREIGN KEY (kamar.id_kamar)
check_in DATE NOT NULL
check_out DATE NOT NULL
jumlah_orang INT NOT NULL
status VARCHAR(50) DEFAULT 'pending'
total_harga DECIMAL(10,2) NOT NULL
created_at DATETIME
updated_at DATETIME
```

### Table: fnb_order
```sql
id_fnb INT PRIMARY KEY AUTO_INCREMENT
id_reservation INT FOREIGN KEY (reservation.id_reservation)
item VARCHAR(150) NOT NULL
qty INT NOT NULL
harga DECIMAL(10,2) NOT NULL
status VARCHAR(50) DEFAULT 'pending'
created_at DATETIME
updated_at DATETIME
```

### Table: payment_fnb
```sql
id_payment_fnb INT PRIMARY KEY AUTO_INCREMENT
id_fnb INT FOREIGN KEY (fnb_order.id_fnb)
total_bayar DECIMAL(10,2) NOT NULL
metode VARCHAR(50) NOT NULL
status VARCHAR(50) DEFAULT 'pending'
paid_at DATETIME
created_at DATETIME
```

### Table: payment_reservation
```sql
id_payment_res INT PRIMARY KEY AUTO_INCREMENT
id_reservation INT FOREIGN KEY (reservation.id_reservation)
total_bayar DECIMAL(10,2) NOT NULL
metode VARCHAR(50) NOT NULL
status VARCHAR(50) DEFAULT 'pending'
paid_at DATETIME
created_at DATETIME
```

---

## Common SQL Queries

### Get User Reservations
```php
$stmt = $conn->prepare("
    SELECT r.*, k.tipe_kamar, k.harga,
           DATEDIFF(r.check_out, r.check_in) as jumlah_hari
    FROM reservation r
    JOIN kamar k ON r.id_kamar = k.id_kamar
    WHERE r.id_user = ?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
```

### Create Reservation
```php
$stmt = $conn->prepare("
    INSERT INTO reservation 
    (id_user, id_kamar, check_in, check_out, jumlah_orang, status, total_harga) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("iississ", 
    $user_id, 
    $room_id, 
    $check_in, 
    $check_out, 
    $guests, 
    $status, 
    $total
);
$stmt->execute();
```

### Update Room Availability
```php
$conn->query("
    UPDATE kamar 
    SET jumlah_tersedia = jumlah_tersedia - 1 
    WHERE id_kamar = $room_id
");
```

### Check Email Exists
```php
$stmt = $conn->prepare("SELECT id_user FROM user WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
```

---

## Status Values

### Reservation Status
- `pending` - Waiting for payment
- `confirmed` - Paid and confirmed
- `cancelled` - Cancelled by user/admin
- `completed` - Stay completed

### Payment Status
- `pending` - Not yet paid
- `paid` - Payment received
- `refunded` - Payment refunded

### F&B Order Status
- `pending` - Order placed
- `preparing` - Being prepared
- `delivered` - Delivered to room
- `completed` - Order completed

---

## Session Variables

After successful login:
```php
$_SESSION['user_id']    // User ID
$_SESSION['nama']       // User name
$_SESSION['email']      // User email
```

Check if admin:
```php
if ($_SESSION['email'] === 'admin@gmail.com') {
    // User is admin
}
```

---

## Error Handling

### Database Connection Error
```php
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
```

### Query Error
```php
if (!$stmt->execute()) {
    error_log("Database error: " . $stmt->error);
    // Handle error
}
```

---

## Security Best Practices

### 1. Always Use Prepared Statements
```php
// ✓ GOOD
$stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
$stmt->bind_param("s", $email);

// ✗ BAD - SQL Injection risk
$query = "SELECT * FROM user WHERE email = '$email'";
```

### 2. Sanitize Input
```php
$name = sanitize($_POST['name']);
$email = sanitize($_POST['email']);
```

### 3. Validate Email
```php
if (!validateEmail($email)) {
    $error = "Invalid email format";
}
```

### 4. Hash Passwords
```php
// Never store plain passwords
$hashed = hashPassword($password);
```

### 5. Protect Pages
```php
requireLogin();  // For all authenticated pages
requireAdmin();  // For admin-only pages
```

---

## Payment Methods

Available payment methods:
- `Bank Transfer`
- `Credit Card`
- `E-Wallet`
- `Cash`

---

## Date Format

Always use `Y-m-d` format for dates in database:
```php
$date = date('Y-m-d');  // 2025-11-29
```

Display format:
```php
echo date('d M Y', strtotime($date));  // 29 Nov 2025
```

---

## File Upload (Future Enhancement)

Structure for file uploads:
```
uploads/
├── profile/        (User profile pictures)
├── rooms/          (Room photos)
└── documents/      (ID documents)
```

---

## API Endpoints (Future Enhancement)

If implementing REST API:

```
GET    /api/rooms              - List all rooms
GET    /api/rooms/{id}         - Get room details
POST   /api/reservations       - Create reservation
GET    /api/reservations/{id}  - Get reservation details
PUT    /api/reservations/{id}  - Update reservation
DELETE /api/reservations/{id}  - Cancel reservation
POST   /api/payments           - Process payment
GET    /api/user/profile       - Get user profile
PUT    /api/user/profile       - Update user profile
```

---

## Testing Utilities

### Test Password Hash
```
http://localhost/Testing%20tubes/test_password.php
```

### Test Database Connection
```
http://localhost/Testing%20tubes/test_connection.php
```

---

## Development Tips

1. **Enable error reporting during development:**
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

2. **Log errors instead of displaying:**
```php
ini_set('log_errors', 1);
ini_set('error_log', 'logs/php_errors.log');
```

3. **Debug database queries:**
```php
echo $conn->error;  // Show last error
```

4. **Check session data:**
```php
print_r($_SESSION);
```

---

**Last Updated:** November 29, 2025  
**Version:** 1.0.0  
**Maintained by:** Development Team
