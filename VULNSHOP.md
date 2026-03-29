# 🛍️ VulnShop — OWASP ZAP Demo Application

> **⚠️ FOR EDUCATIONAL USE ONLY**  
> This application is intentionally insecure. Run it only on a local machine,  
> never expose it to the internet, and never scan anything you don't own.

---

## What Is This?

VulnShop is a deliberately vulnerable PHP shopping site built specifically for  
the *Scaling Security with OWASP ZAP* presentation. It contains real, working  
examples of every vulnerability category in the OWASP Top 10, giving ZAP a rich  
target to demonstrate its scanning capabilities against.

---

## 📁 Project Structure

```
vulnshop/
├── index.php            Homepage — product grid
├── login.php            Auth — SQLi + info disclosure
├── register.php         Registration — plain-text passwords
├── search.php           Search — SQLi + Reflected XSS
├── product.php          Product detail — SQLi + Stored XSS
├── profile.php          Profile — IDOR + SQLi + data exposure
├── contact.php          Contact form — Reflected XSS
├── dashboard.php        Post-login page (ZAP logged-in indicator)
├── logout.php           Session destroy (EXCLUDED from ZAP scope)
├── ajax-page.php        JS-rendered page (AJAX Spider demo)
├── nav.php              Shared navigation bar
├── db.php               Database connection
├── setup.sql            Database schema + seed data
├── robots.txt           Reveals /admin, /backup, /config
├── docker-compose.yml   Run without XAMPP
│
├── api/
│   ├── products.php     GET /api/products.php — list products
│   ├── user.php         GET /api/user.php?id=N — EXPOSES PASSWORDS
│   ├── search.php       GET /api/search.php?q=X — SQLi + XSS in JSON
│   └── openapi.yaml     OpenAPI 3.0 spec — import directly into ZAP
│
├── admin/
│   └── index.php        Admin panel — broken access control
│
├── css/
│   └── style.css        Application stylesheet
│
└── .zap/
    ├── zap-automation.yaml   Full scan blueprint (YAML)
    ├── rules.tsv             Alert severity overrides
    ├── AddAuthHeader.js      HttpSender script (Feature 4 demo)
    └── LoginAsAdmin.zst      Zest authentication script (Feature 5 demo)

└── .github/
    └── workflows/
        └── security-scan.yml  GitHub Actions CI/CD pipeline
```

---

## 🚀 Setup — Option A: XAMPP (Recommended for Live Demo)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) with Apache + MySQL
- [OWASP ZAP](https://www.zaproxy.org/download/) latest stable
- Firefox

### Steps

**1. Copy files**
```
Copy the entire vulnshop/ folder to:
C:\xampp\htdocs\Siddhant\VulnShop\
```

**2. Start XAMPP**
- Open XAMPP Control Panel
- Start **Apache** → wait for green
- Start **MySQL** → wait for green

**3. Create the database**
- Open browser → http://localhost/phpmyadmin
- Click **Import** → choose `setup.sql` → click Go
- Confirm `vulnshop` database appears with 3 tables

**4. Verify the app**
- Visit: http://localhost/Siddhant/VulnShop/
- You should see the product grid homepage

**5. Configure Firefox to proxy through ZAP**
```
Firefox → about:preferences → Network Settings → Settings
  ☑ Manual proxy configuration
  HTTP Proxy: 127.0.0.1   Port: 8081
  ☑ Also use this proxy for HTTPS
  No proxy for: [CLEAR THIS FIELD COMPLETELY]
```
> ZAP's default proxy port is 8080. If something else is on 8080,  
> check ZAP → Tools → Options → Network → Local Servers/Proxies.

**6. Set ZAP scope**
- In Firefox visit http://localhost/Siddhant/VulnShop/
- In ZAP → Sites tree → right-click `localhost` → Include in Context → Default Context
- Include pattern: `http://localhost/Siddhant/VulnShop.*`
- Exclude pattern: `http://localhost/Siddhant/VulnShop/logout\.php`

---

## 🐳 Setup — Option B: Docker (No XAMPP Needed)

```bash
# Clone / copy this folder, then:
docker-compose up -d

# Wait for health checks to pass (~20 seconds)
# VulnShop is now at: http://localhost:8080/VulnShop/
```

**ZAP files and Docker:** `.zap/zap-automation.yaml`, `.zap/LoginAsAdmin.zst`, and the primary `servers` URL in `api/openapi.yaml` are set to `http://localhost:8080/VulnShop` so they work with this compose stack without edits. On XAMPP, replace that base URL with yours (for example `http://localhost/Siddhant/VulnShop`) or re-import/re-record in ZAP.

---

## 🧪 Test Credentials

| Username | Password  | Role  |
|----------|-----------|-------|
| john     | password1 | user  |
| jane     | letmein   | user  |
| admin    | admin123  | admin |

---

## 🔍 Vulnerability Map

| File            | Vulnerability                         | OWASP Category           |
|-----------------|---------------------------------------|--------------------------|
| login.php       | SQL Injection (`' OR '1'='1' --`)     | A03 Injection            |
| login.php       | Username enumeration                  | A07 Auth Failures        |
| login.php       | No rate limiting / lockout            | A07 Auth Failures        |
| search.php      | SQL Injection (LIKE query)            | A03 Injection            |
| search.php      | Reflected XSS (search term echoed)    | A03 Injection            |
| product.php     | SQL Injection (integer id)            | A03 Injection            |
| product.php     | Stored XSS (comments saved raw)       | A03 Injection            |
| profile.php     | IDOR (`?user_id=N` — no auth check)   | A01 Broken Access Ctrl   |
| profile.php     | Sensitive data (password in UI)       | A02 Crypto Failures      |
| contact.php     | Reflected XSS (name + message)        | A03 Injection            |
| register.php    | Plain-text password storage           | A02 Crypto Failures      |
| admin/index.php | Broken Access Control (no role check) | A01 Broken Access Ctrl   |
| api/user.php    | Sensitive data (password in JSON)     | A02 Crypto Failures      |
| api/user.php    | No authentication required            | A01 Broken Access Ctrl   |
| api/search.php  | SQL Injection                         | A03 Injection            |
| api/search.php  | XSS reflected in JSON response        | A03 Injection            |
| All forms       | No CSRF protection                    | A01 Broken Access Ctrl   |
| robots.txt      | Reveals sensitive paths               | A05 Misconfiguration     |
| *.php           | Raw MySQL errors printed on screen    | A05 Misconfiguration     |

---

## 🎬 Demo Script — ZAP Features in Order

### Feature 1 — Standard Spider
```
ZAP → Tools → Spider
Starting point: http://localhost/Siddhant/VulnShop/
Context: Default Context
Click Start Scan

POINT OUT:
✓ robots.txt was read — /admin, /backup, /config discovered
✓ All static HTML pages found instantly
✗ ajax-page.php appears but has 0 children (JS content missed)
```

### Feature 2 — AJAX Spider
```
ZAP → Tools → AJAX Spider
Starting point: http://localhost/Siddhant/VulnShop/ajax-page.php
Browser: Firefox
Click Start Scan — a real browser window opens

POINT OUT:
✓ product.php?id=1 through ?id=6 now appear under ajax-page.php
✓ These were NEVER in the raw HTML source
✓ Standard spider: 0 links. AJAX spider: 6 links.
```

### Feature 3 — OpenAPI Import
```
ZAP → File → New Session   (clear Sites tree first)
ZAP → Import → Import an OpenAPI definition from a file
Browse to: .../VulnShop/api/openapi.yaml
Click Import

POINT OUT:
✓ Sites tree instantly populates with /api/products.php,
  /api/user.php, /api/search.php
✓ ZAP already knows parameter names and methods
✓ No crawling needed — instant attack surface map
```

### Feature 4 — HttpSender Script
```
ZAP → View → Show Tab → Scripts
Scripts tree → HttpSender → right-click → New Script
Name: AddAuthHeader | Language: JavaScript
Paste: .zap/AddAuthHeader.js content
Click Save → tick the checkbox to enable

Browse any VulnShop page in Firefox
ZAP → History tab → latest request → Request tab

POINT OUT:
✓ X-Demo-User: john on EVERY request
✓ Authorization: Bearer demo-token-12345 on EVERY request
Toggle off → headers gone. Toggle on → headers back.
```

### Feature 5 — Authentication + Zest Macro
```
Session Properties → Authentication:
  Method: Form-based
  Login URL: http://localhost/Siddhant/VulnShop/login.php
  POST data: username={%username%}&password={%password%}
  Logged-in indicator:  Welcome back
  Logged-out indicator: Login

Add users: john/password1, admin/admin123

Tools → Spider → User: john → Start Scan
→ dashboard.php and profile.php NOW appear (were missing before)

Tools → Record a New Zest Script
Name: LoginAsAdmin | Type: Authentication
Start Recording → log in as admin/admin123 → Stop
Scripts → Zest → LoginAsAdmin → right-click → Run
```

### Feature 6 — Active Scan + Report
```
Right-click VulnShop in Sites tree → Attack → Active Scan
Context: Default Context | User: john
Click Start Scan

Watch Alerts tab — findings appear in real time:
  🔴 SQL Injection          (search.php, product.php, login.php, ...)
  🟠 Cross Site Scripting   (Reflected on search.php, Stored on product.php)
  🟠 IDOR                   (profile.php?user_id=)
  🟡 Missing security headers, plain-text passwords

Report → Generate Report → HTML → Save to Desktop
Open in browser → show executive summary + individual alert detail
```

---

## 🔄 CI/CD — Running ZAP in GitHub Actions

The `.github/workflows/security-scan.yml` file runs ZAP automatically:

| Trigger             | Scan type      | Duration  | Blocks merge? |
|---------------------|----------------|-----------|---------------|
| Pull Request        | Baseline       | ~5 min    | ✅ Yes (HIGH) |
| Push to develop     | Baseline       | ~5 min    | ✅ Yes (HIGH) |
| Push to main        | Full active    | ~30-60min | ✅ Yes (HIGH) |
| Nightly (02:00 UTC) | Full active    | ~30-60min | ✅ Yes (HIGH) |

Results appear in:
- **Artefacts:** Actions tab → your run → Download HTML report
- **Security tab:** Security → Code scanning alerts (SARIF)

---

## 🔧 Running ZAP Headlessly (Without GUI)

```bash
# Using YAML Automation Framework (recommended):
zap.sh -daemon -autorun .zap/zap-automation.yaml -port 8090

# Using Docker:
docker run --rm \
  -v $(pwd):/zap/wrk:rw \
  ghcr.io/zaproxy/zaproxy:stable \
  zap.sh -daemon -autorun /zap/wrk/.zap/zap-automation.yaml

# Quick baseline (no config file needed):
docker run --rm ghcr.io/zaproxy/zaproxy:stable \
  zap-baseline.py -t http://localhost:8080/VulnShop -r report.html
```

---

## 📚 References

- OWASP ZAP: https://www.zaproxy.org/docs/
- ZAP Automation Framework: https://www.zaproxy.org/docs/automate/automation-framework/
- ZAP GitHub Actions: https://github.com/marketplace/actions/owasp-zap-baseline-scan
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- IBM Cost of a Data Breach 2024: https://www.ibm.com/reports/data-breach
