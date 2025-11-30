# 🚀 Quick Start Guide

Get your Hotel Management System up and running in 5 minutes!

## ⚡ Fast Setup (5 Minutes)

### Step 1: Start XAMPP (1 minute)
```
1. Open XAMPP Control Panel
2. Click "Start" on Apache
3. Click "Start" on MySQL
4. Wait until both show green
```

### Step 2: Create Database (2 minutes)
```
1. Open http://localhost/phpmyadmin
2. Click "New" in left sidebar
3. Database name: hotel_management
4. Click "Create"
5. Click "Import" tab
6. Choose file: database/hotel_management.sql
7. Click "Go"
```

### Step 3: Configure (30 seconds)
```
Edit: config/database.php

Change if needed:
- DB_USER (default: root)
- DB_PASS (default: empty)
```

### Step 4: Access Application (30 seconds)
```
Open browser:
http://localhost/Testing%20tubes/index.html

Atau (jika spasi berfungsi):
http://localhost/Testing tubes/index.html
```

### Step 5: Login (1 minute)
```
Admin Login:
Email: admin@gmail.com
Password: admin123

Or Register as new user
```

## ✅ Verification Checklist

After setup, verify these work:

- [ ] Homepage loads with video background
- [ ] Login page accessible
- [ ] Register page accessible
- [ ] Admin can login
- [ ] User can register
- [ ] Dashboard loads after login
- [ ] No database connection errors

## 🎯 First Steps After Login

### As Admin:
1. ✅ Change admin password (important!)
2. ✅ Check all rooms in Room Management
3. ✅ View dashboard statistics
4. ✅ Test creating a new room

### As User:
1. ✅ Update your profile
2. ✅ Browse available rooms
3. ✅ Try booking a room
4. ✅ Test payment flow

## 🐛 Quick Fixes

### "Connection failed"
```
→ Check if MySQL is running in XAMPP
→ Verify database name is hotel_management
→ Check config/database.php credentials
```

### "Table doesn't exist"
```
→ Re-import database/hotel_management.sql
→ Check if all 7 tables are created
```

### "Blank page"
```
→ Check if Apache is running
→ Verify file path is correct
→ Check PHP error log
```

### "Admin can't login"
```
→ Open test_password.php to verify password
→ Check if admin exists in user table
→ Password should be: admin123
```

## 📱 Test URLs

Once setup, test these pages:

```
Landing Page:
http://localhost/Testing%20tubes/index.html

Login:
http://localhost/Testing%20tubes/login.php

Register:
http://localhost/Testing%20tubes/register.php

Admin Dashboard (after login):
http://localhost/Testing%20tubes/admin/dashboard.php

User Dashboard (after login):
http://localhost/Testing%20tubes/user/dashboard.php

Password Tester:
http://localhost/Testing%20tubes/test_password.php

Connection Test:
http://localhost/Testing%20tubes/test_connection.php
```

## 🎨 Quick Customization

### Change Hotel Name:
```
Files to edit:
- index.html (line ~10)
- All dashboard pages
- Database kamar table
```

### Change Colors:
```
Edit: assets/css/dashboard.css
Find: #f0b343 and #ff7a89 (brand colors: gold -> coral pink)
Replace with your colors
```

### Add Logo:
```
Replace: assets/logo.png
With your logo image
```

## 💡 Pro Tips

1. **Bookmark frequently used URLs**
   - phpMyAdmin
   - Admin dashboard
   - User dashboard

2. **Keep XAMPP running**
   - Don't close XAMPP while testing
   - Monitor Apache and MySQL status

3. **Use different browsers**
   - Test in Chrome, Firefox, Edge
   - Check mobile view (F12 > Toggle device)

4. **Regular backups**
   - Export database regularly
   - Backup project folder

5. **Clear cache if needed**
   - Hard refresh: Ctrl + F5 (Windows)
   - Hard refresh: Cmd + Shift + R (Mac)

## 📊 Sample Data Included

After import, you'll have:
- ✅ 1 Admin user (admin@gmail.com)
- ✅ 4 Room types (Deluxe, Suite, Standard, Presidential)
- ✅ Sample room descriptions
- ✅ Default prices

## 🔄 Reset Everything

If you need to start fresh:

```sql
1. Open phpMyAdmin
2. Select hotel_management database
3. Click "Operations" tab
4. Click "Drop the database"
5. Create new database
6. Import SQL file again
```

## 🎓 Learn More

For detailed documentation:
- See README.md for full features
- See INSTALL.md for detailed setup
- See CHANGELOG.md for version info

## 🆘 Need Help?

Common resources:
1. Check README.md first
2. Read INSTALL.md for details
3. Check error logs in XAMPP
4. Google the error message
5. Ask your instructor

## 🎉 You're Ready!

If all checks pass, you're ready to:
- Explore all features
- Test booking flow
- Manage rooms as admin
- Create test reservations

---

**Enjoy your Hotel Management System! 🏨**

Made with ❤️ for educational purposes
