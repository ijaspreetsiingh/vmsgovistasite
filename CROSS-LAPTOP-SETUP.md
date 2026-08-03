# Cross-Laptop Access Setup Guide

## Changes Made for Dynamic IP Support

The project has been configured to work across different laptops/IP addresses automatically. Here's what was changed:

### 1. Dynamic SITE_URL (config/db.php)
- **Before:** Hardcoded `http://localhost/vms/touriza-htm`
- **After:** Dynamic detection based on current request
  ```php
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $path = dirname($_SERVER['SCRIPT_NAME'] ?? '/vms/touriza-htm');
  $path = rtrim(str_replace('\\', '/', $path), '/');
  define('SITE_URL', $protocol . '://' . $host . $path);
  ```

### 2. Session Cookie Settings (includes/auth.php)
- **Before:** Missing domain parameter
- **After:** Empty domain to allow cross-IP access
  ```php
  session_set_cookie_params([
      'lifetime' => SESSION_TIMEOUT,
      'path'     => '/',
      'domain'   => '',  // Empty domain allows cross-IP access
      'secure'   => false,
      'httponly' => true,
      'samesite' => 'Lax',
  ]);
  ```

### 3. Image Paths
- All image URLs now use the dynamic `SITE_URL` constant
- The `packageImageUrl()` function automatically prepends the correct base URL
- No hardcoded localhost references in image paths

## How to Use on Different Laptops

### Step 1: Find Your IP Address
On the hosting laptop, run:
```bash
ipconfig
```
Look for "IPv4 Address" (e.g., 192.168.1.100)

### Step 2: Access from Another Laptop
Open browser on the second laptop and navigate to:
```
http://192.168.1.100/vms/touriza-htm
```
Replace `192.168.1.100` with your actual IP address.

### Step 3: Admin Panel Access
Admin panel will automatically work at:
```
http://192.168.1.100/vms/touriza-htm/admin/
```

## Important Notes

### Database Configuration
The database host remains `localhost` because:
- MySQL runs on the same machine as the web server
- Remote laptops access the web server, not the database directly
- The web server (localhost) connects to the database

### XAMPP Configuration
If using XAMPP, ensure:
1. Apache is running
2. MySQL is running
3. Firewall allows port 80 (HTTP) access
4. Both laptops are on the same network

### Firewall Settings
Windows Firewall may block incoming connections. To allow:
1. Open Windows Defender Firewall
2. Go to "Allow an app through Windows Defender Firewall"
3. Enable "Apache HTTP Server" for both Private and Public networks
4. Or create an inbound rule for port 80

## Troubleshooting

### Images Not Loading
- Check that `SITE_URL` is correct by adding `<?php echo SITE_URL; ?>` to any page
- Verify the uploads folder exists: `uploads/packages/`
- Check file permissions on uploads folder

### Admin Panel Not Working
- Clear browser cookies and cache
- Ensure session cookies are being set
- Check that both laptops can access the main site first

### Can't Connect from Other Laptop
- Verify both laptops are on the same network
- Check firewall settings on hosting laptop
- Ensure Apache is running and listening on all interfaces (0.0.0.0:80)
- Try pinging the hosting laptop IP from the other laptop

## Testing
To verify the setup is working:
1. Open the site on hosting laptop using `http://localhost/vms/touriza-htm`
2. Open the same site on another laptop using the IP address
3. Check that images load correctly on both
4. Test admin panel login on both laptops
