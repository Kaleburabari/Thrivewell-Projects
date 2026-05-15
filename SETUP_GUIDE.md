# ThriveWell OS - Complete Setup Guide

## 📋 Prerequisites
- **PHP 8.3+** (currently running PHP 8.5.7-dev ✅)
- **Node.js 18+** (currently running v24.15.0 ✅)
- **npm 11+** (currently running v11.4.2 ✅)
- **Composer 2.9+** (currently running v2.9.7 ✅)

## ⚠️ Current Network Status

**Package registries are currently blocked (HTTP 403):**
- ❌ Packagist (Laravel packages)
- ❌ npm Registry (React/Inertia packages)

**Solution:** The project runs in **zero-dependency mode** using master-spec-driven fallback. All core features work without external packages.

## 🚀 Quick Start (Two Terminals)

### Terminal 1: Start Laravel Backend
```bash
php artisan serve
```
✅ Listens on http://127.0.0.1:8000

### Terminal 2: Start Frontend Dev Server
```bash
npm run dev
```
✅ Listens on http://127.0.0.1:3000 (proxies to backend)

## 📖 Default Login Credentials

| Role | Email | Password |
|---|---|---|
| **Intern Counsellor** | `intern@thrivewell.test` | `password` |
| **Superadmin** | `admin@thrivewell.test` | `password` |
| **Client** | `client@thrivewell.test` | `password` |

## 🛠️ One-Time Setup (Optional - Database Already Seeded)

If you need to reset the database:

```bash
# Reset and seed fresh database
php artisan migrate:fresh --seed

# Or just re-seed with existing migrations
php artisan db:seed
```

## 📁 Project Structure

```
Thrivewell-Projects/
├── app/                          # PHP application code
│   ├── Http/Controllers/        # API endpoints
│   ├── Models/                  # Database models
│   ├── Services/                # Business logic
│   └── Policies/                # Authorization
├── config/                       # Laravel configuration
├── routes/                       # API & web routes
├── resources/                    # Frontend assets
│   └── js/                      # React components (coming)
├── public/                       # Web root & compiled assets
├── storage/                      # Database & logs
│   └── database/thrivewell.sqlite  # SQLite database
├── scripts/                      # Build & dev scripts
├── tests/                        # Test suite
├── database/                     # Migrations & seeders
├── artisan                       # Laravel CLI
├── composer.json                 # PHP dependencies
├── package.json                  # Node dependencies
└── .env.example                  # Environment template
```

## 🔑 Available Artisan Commands

```bash
php artisan key:generate           # Generate APP_KEY
php artisan migrate               # Run database migrations
php artisan migrate:fresh --seed  # Reset & seed database
php artisan db:seed               # Seed with demo data
php artisan serve                 # Start dev server (port 8000)
php artisan test                  # Run test suite
```

## 📊 Available npm Scripts

```bash
npm run dev                    # Start frontend dev server (port 3000)
npm run build                  # Build assets to public/build
npm run check:package-access   # Check if registries are reachable
npm run migrate:official       # Migrate to official Laravel/Inertia (when registries available)
```

## 🐛 Troubleshooting

### "Failed to connect to 127.0.0.1:8000"
- **Solution:** Make sure `php artisan serve` is running in another terminal before opening http://127.0.0.1:3000

### "npm warn Unknown env config http-proxy"
- **Fixed:** `.npmrc` configuration applied ✅

### "HTTP 403 - Package registry blocked"
- **Status:** Expected in this environment
- **Workaround:** Continue with zero-dependency fallback features
- **Future:** See `docs/package-access-unblock.md` for network allowlisting

### Database locked or schema issues
```bash
# Reset everything
php artisan migrate:fresh --seed
```

## 📖 Master Specification

All development follows `THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php`

**Core Principles:**
- ✅ Do NOT remove existing features
- ✅ Only improve or extend functionality
- ✅ Maintain clinical/AI safety boundaries
- ✅ Preserve audit logging throughout

## 🔄 Next Steps

1. **Login** with credentials above to see the dashboard
2. **Explore features**: sessions, credentials, CPD, wallet, etc.
3. **Review specification** in `THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php`
4. **Expand features** using existing patterns
5. **Migrate to official Laravel/Inertia** when package access is available

## 📞 Support

For issues:
1. Check this guide and README.md
2. Review `THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php`
3. Check network/proxy settings for package registry issues
4. See `docs/` folder for detailed troubleshooting

---

✅ **Your ThriveWell OS is ready to develop!**
