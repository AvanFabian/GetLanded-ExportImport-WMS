# 🤝 Contributing to GetLanded
First off, thank you for considering contributing to **GetLanded**! 🎉

The following is a set of guidelines for contributing to GetLanded. These are mostly guidelines, not rules. Use your best judgment, and feel free to propose changes to this document in a pull request.

## 🛠 Development Workflow

### 1. Branching Strategy
We follow a simplified **Git Flow**:
- **`main`**: Production-ready code.
- **`develop`**: Integration branch for next release.
- **`feature/name`**: New features (branch off `develop`).
- **`fix/issue`**: Bug fixes (branch off `develop` or `main` for hotfixes).

### 2. Setting Up
1.  Fork the repository.
2.  Clone your fork: `git clone <your-fork-url>`
3.  Install dependencies: `composer install && npm install`
4.  Copy `.env`: `cp .env.example .env`
5.  Generate key: `php artisan key:generate`
6.  Migrate & Seed: `php artisan migrate:fresh --seed`

### 3. Making Changes
- Write clean, self-documenting code.
- Follow **PSR-12** coding standards.
- Ensure all logic is **Tenant Aware** (always check for `TenantScope`).

### 4. Running Tests
Before submitting a PR, ensure all tests pass:
```bash
php artisan test
```

## 🎨 Coding Standards

We use **Laravel Pint** to maintain code style.
```bash
./vendor/bin/pint
```

### Key Rules
- **Controllers**: Keep them thin. Move business logic to `Services/`.
- **Models**: Use `fillable` or `guarded`. Always apply `TenantScope` via traits or global boot.
- **Views**: Use Blade Components (`x-input`, `x-card`) effectively.

## 📝 Commit Messages
- Use the imperative mood ("Add feature" not "Added feature").
- Reference issue numbers if applicable.

Example:
```
feat: implement batch tracking service
fix: resolve pdf generation timeout #123
docs: update architecture diagram
```

## 🔒 Security Vulnerabilities
If you discover a security vulnerability, please send an e-mail to security@avandigital.id. All security vulnerabilities will be promptly addressed.
