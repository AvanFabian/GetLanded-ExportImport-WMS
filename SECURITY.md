# Security Implementation Guide

## 🛡️ GetLanded Security Implementation
This document details the multi-layered security architecture protecting **GetLanded** data.
- DDoS Protection & Rate Limiting
- Security Headers
- Request Validation & Blocking
- Session Security
- Activity Logging
- CSRF Protection
- XSS Protection
- SQL Injection Prevention

---

## 🛡️ Security Features Implemented

### 1. **Rate Limiting (DDoS Protection)**

#### **Web Routes** (`throttle:web`)
- **Limit**: 60 requests per minute per user/IP
- **Applied to**: All authenticated routes
- **Purpose**: Prevent automated attacks and excessive usage

#### **Authentication Routes** (`throttle:auth`)
- **Limit**: 5 attempts per minute per IP
- **Applied to**: Login, register, password reset
- **Purpose**: Prevent brute force attacks

#### **Sensitive Operations** (`throttle:sensitive`)
- **Limit**: 3 requests per minute per user/IP
- **Applied to**: Account deletion, critical updates
- **Purpose**: Extra protection for dangerous operations

#### **Password Reset** (`throttle:password-reset`)
- **Limit**: 3 requests per hour per email/IP
- **Applied to**: Password reset requests
- **Purpose**: Prevent password reset abuse

**Configuration**: `app/Providers/RateLimitServiceProvider.php`

---

### 2. **Security Headers** (SecurityHeaders Middleware)

Automatically adds security headers to all responses:

```
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains (production only)
Content-Security-Policy: [detailed policy]
Referrer-Policy: strict-origin-when-cross-origin
Permissions-Policy: geolocation=(), microphone=(), camera=()
```

**Purpose**:
- Prevent clickjacking attacks
- Prevent MIME type sniffing
- Enable XSS protection in browsers
- Enforce HTTPS in production
- Control resource loading (CSP)
- Limit browser permissions

**File**: `app/Http/Middleware/SecurityHeaders.php`

---

### 3. **Suspicious Request Blocking** (BlockSuspiciousRequests Middleware)

Automatically blocks requests containing:

#### **SQL Injection Patterns**
- UNION SELECT statements
- DROP TABLE commands
- DELETE FROM, INSERT INTO, UPDATE SET
- Multiple query attempts

#### **XSS Patterns**
- `<script>` tags
- `javascript:` protocol
- Event handlers (onerror, onclick)

#### **Path Traversal**
- `../` patterns
- Directory traversal attempts

#### **Command Injection**
- Shell commands (ls, cat, rm, chmod, wget, curl)
- Pipe operators

#### **Blocked User Agents**
- sqlmap, nikto, nmap (attack tools)
- masscan, zgrab
- python-requests, curl, wget (automated scripts)

#### **Request Size Limits**
- Maximum 10MB per request
- Returns 413 (Payload Too Large) if exceeded

#### **File Upload Security**
- **MIME Validation**: Strict `mimes:xlsx,csv,xls` check for imports.
- **Processing Limits**: `set_time_limit(600)` and optimized memory chunks for admin imports to prevent DoS.

**All blocked attempts are logged to `security.log`**

**File**: `app/Http/Middleware/BlockSuspiciousRequests.php`

---

### 4. **Session Security**

Configured in `.env`:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120          # 2 hours
SESSION_SECURE_COOKIE=false   # Set to true in production with HTTPS
SESSION_HTTP_ONLY=true        # Prevent JavaScript access
SESSION_SAME_SITE=lax         # CSRF protection
```

**Production Settings**:
```env
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
```

---

### 5. **CSRF Protection**

Laravel's built-in CSRF protection is enabled for all POST, PUT, DELETE, PATCH requests.

**Implementation**:
- All forms include `@csrf` directive
- CSRF token validated automatically
- Invalid tokens return 419 error (custom page created)

**Error Page**: `resources/views/errors/419.blade.php`

---

### 6. **Password Security**

Configured in `config/security.php`:

```php
'password' => [
    'min_length' => 8,
    'require_uppercase' => true,
    'require_lowercase' => true,
    'require_numbers' => true,
    'require_special_chars' => false,
    'prevent_common' => true,
]
```

**Hashing**: Uses bcrypt with 12 rounds (configurable via `BCRYPT_ROUNDS`)

---

### 7. **Activity Logging**

All user actions are automatically logged to `storage/logs/activity.log`:

**Logged Actions**:
- Create operations (POST)
- Update operations (PUT/PATCH)
- Delete operations (DELETE)

**Logged Information**:
- User ID, email, role
- Action description
- URL, method, IP address
- User agent, timestamp

**File**: `app/Http/Middleware/LogUserActivity.php`

---

### 8. **Custom Error Pages**

Professional error pages for common HTTP errors:

- **403** - Access Denied (shows current user role)
- **404** - Page Not Found
- **419** - Session Expired (CSRF token expired)
- **429** - Too Many Requests (with countdown timer)
- **500** - Internal Server Error
- **503** - Service Unavailable

All pages maintain app layout and branding.

---

## ⚙️ Configuration Files

### **config/security.php**
Central security configuration file containing:
- Rate limiting settings
- Security headers
- CSP policies
- IP whitelist/blacklist
- Session security
- Password requirements
- Failed login settings

### **.env Configuration**

```env
# Rate Limiting
RATE_LIMIT_WEB_REQUESTS=60
RATE_LIMIT_AUTH_REQUESTS=5
RATE_LIMIT_SENSITIVE_REQUESTS=3

# Request Blocking
BLOCK_SUSPICIOUS_REQUESTS=true
LOG_SUSPICIOUS_ATTEMPTS=true
MAX_REQUEST_SIZE=10485760

# Content Security Policy
CSP_ENABLED=true

# IP Management
IP_WHITELIST=
IP_BLACKLIST=

# Password Policy
PASSWORD_MIN_LENGTH=8

# Login Security
MAX_LOGIN_ATTEMPTS=5
LOGIN_LOCKOUT_DURATION=60

# Session Security
SESSION_SECURE_COOKIE=false  # true in production
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

---

## 📋 Production Checklist

### **Before Deployment**:

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Enable HTTPS
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `SESSION_SAME_SITE=strict`
- [ ] Review `IP_WHITELIST` and `IP_BLACKLIST`
- [ ] Configure proper `MAIL_` settings for notifications
- [ ] Set `LOG_LEVEL=error` or `warning`
- [ ] Review rate limits (may need to adjust for traffic)
- [ ] Enable database backups
- [ ] Set up monitoring/alerting
- [ ] Review all `.env` values
- [ ] Test all error pages

### **After Deployment**:

- [ ] Monitor `security.log` for blocked attempts
- [ ] Monitor `activity.log` for unusual patterns
- [ ] Test rate limiting with real traffic
- [ ] Verify HTTPS is working
- [ ] Test all authentication flows
- [ ] Verify security headers are present
- [ ] Run security scan (OWASP ZAP, etc.)
- [ ] Set up automated security updates

---

## 🧪 Testing Security Features

### **Test Rate Limiting**

```bash
# Test login rate limit (should block after 5 attempts)
for i in {1..10}; do
    curl -X POST http://localhost:8000/login \
        -d "email=test@test.com&password=wrong"
done
```

### **Test Suspicious Patterns**

```bash
# Should be blocked (SQL injection attempt)
curl "http://localhost:8000/products?search=1' UNION SELECT * FROM users--"

# Should be blocked (XSS attempt)
curl "http://localhost:8000/products?search=<script>alert(1)</script>"
```

### **Test Security Headers**

```bash
curl -I http://localhost:8000
# Look for X-Frame-Options, X-Content-Type-Options, etc.
```

### **Test 429 Error Page**

1. Make rapid requests to trigger rate limit
2. Verify custom 429 page is shown
3. Check countdown timer works

---

## 📊 Monitoring & Alerts

### **Log Files to Monitor**

```bash
# Security events
tail -f storage/logs/security.log

# User activities
tail -f storage/logs/activity.log

# General errors
tail -f storage/logs/laravel.log
```

### **Key Metrics to Watch**

1. **Failed Login Attempts**
   ```bash
   grep "Suspicious Request" storage/logs/security.log | wc -l
   ```

2. **Blocked IPs**
   ```bash
   grep "Blocked user agent" storage/logs/security.log
   ```

3. **Rate Limit Hits**
   ```bash
   # Check nginx/apache access logs for 429 responses
   ```

4. **Unusual Activity Patterns**
   - Multiple failed logins from same IP
   - Unusual time-of-day access
   - Requests from foreign countries (if not expected)

---

## 🔧 Advanced Security (Optional)

### **1. Enable Redis for Rate Limiting**

For better performance with high traffic:

```bash
composer require predis/predis
```

Update `.env`:
```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### **2. Install Laravel Sanctum (API Security)**

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

### **3. Add Two-Factor Authentication**

```bash
composer require laravel/fortify
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

### **4. Install Security Scanner**

```bash
composer require --dev enlightn/security-checker
php artisan security-checker:security
```

### **5. Add IP Geolocation Blocking**

```bash
composer require stevebauman/location
```

### **6. Enable Database Encryption**

For sensitive data (credit cards, SSNs), use Laravel's encryption:

```php
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString('sensitive data');
$decrypted = Crypt::decryptString($encrypted);
```

---

## 🚨 Incident Response

### **If Attack Detected**:

1. **Identify the attack vector**
   - Check `security.log`
   - Check `activity.log`
   - Check server access logs

2. **Block the attacker**
   - Add IP to `IP_BLACKLIST` in `.env`
   - Add IP to firewall/WAF

3. **Assess damage**
   - Check database for unauthorized changes
   - Review recent user activities
   - Check for data exfiltration

4. **Notify stakeholders**
   - Alert system administrators
   - Notify affected users if needed
   - Document the incident

5. **Prevent future attacks**
   - Update security rules
   - Patch vulnerabilities
   - Review and strengthen policies

---

## 📚 Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [Content Security Policy Guide](https://content-security-policy.com/)
- [Web Application Firewall (WAF)](https://www.cloudflare.com/learning/ddos/glossary/web-application-firewall-waf/)

---

## ✅ Security Status

**Current Security Level**: ⭐⭐⭐⭐☆ (4/5 Stars)

**Implemented**:
✅ Rate Limiting (DDoS Protection)  
✅ Security Headers  
✅ Request Validation & Blocking  
✅ CSRF Protection  
✅ XSS Prevention  
✅ SQL Injection Prevention  
✅ Session Security  
✅ Activity Logging  
✅ Custom Error Pages  

**Optional Enhancements**:
⚠️ Two-Factor Authentication  
⚠️ IP Geolocation Blocking  
⚠️ Web Application Firewall (WAF)  
⚠️ Automated Security Scanning  
⚠️ Intrusion Detection System (IDS)  

---

---

## ⚖️ Compliance & Data Sovereignty

### **UU PDP (Indonesia Personal Data Protection)**
GetLanded is designed to comply with **UU No. 27 Tahun 2022**:

1.  **Data Isolation**: 
    - Every database query is scoped by `company_id`.
    - Tenant data is logically isolated; User A cannot query User B's stock.
    - Implementation: `app/Models/Scopes/TenantScope.php`.

2.  **Right to Erasure (Soft vs Hard Delete)**:
    - Default behavior is **Soft Delete** (`deleted_at`).
    - "Trash" module allows authorized admins to restore or permanently delete records.
    - Permanent deletion wipes data from the primary database (Encryption keys are rotated).

3.  **Audit Trails**:
    - All data modifications (Create, Update, Delete) are logged in `activity_log`.
    - Logs include: **Who** (User ID), **When** (Timestamp), **What** (Old vs New values), and **Where** (IP Address).

4.  **Encryption**:
    - Passwords are hashed using **Bcrypt**.
    - Session data is encrypted at rest (if `SESSION_ENCRYPT=true`).
    - Communication is enforced over **HTTPS/TLS 1.2+**.

### **GDPR Readiness**
- **Data Portability**: Users can export all Master Data (Products, Customers) and Transactions via Excel/PDF.
- **Consent**: Cookie consent logic is prepared in the frontend layer.
- **Breach Notification**: Security architecture includes monitoring hooks for `security.log` to alert admins of suspicious spikes.

