# 📜 GetLanded Changelog
Notable changes to the **GetLanded** ecosystem. will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Phase S (Documentation)**:
    - Rebranded documentation with "GetLanded" identity.
    - Added comprehensive Architecture guide.
- **Phase I (Import Engine 2.0)**:
    - **High-Volume Support**: Validated stability with 25,000+ SKU records in a single upload.
    - **Native XLSX**: Dumped legacy CSV requirement for robust Excel parsing (`maatwebsite/excel`).
    - **Performance**: Implemented `WithChunkReading` (500 rows/chunk) to cap RAM usage.
    - **Stress Test**: Included `stress_test_products.xlsx` generator for "Real World" simulation.
- **Phase R.3 (Hero Image)**:
    - Added `hero-warehouse.jpg` with Emerald Glow and Glass-morphism effects.
    - Implemented floating status badges on Landing Page.
- **Phase R.2 (Extended)**:
    - **Premium Auth UI**: Split-screen Login/Register layouts.
    - **SEO**: Dynamic OpenGraph `<x-meta-tags />` component.
    - **I18n**: Cookie-based language persistence for guests.

### Changed
- **Branding**: Renamed application from "Warehouse Inventory" to "GetLanded".
- **Middleware**: Refactored `SetLocaleMiddleware` to prioritize Session > Cookie > User > Header.

## [v1.2.0] - Phase Q - 2026-01-20

### Added
- **Localization (i18n)**:
    - Full English/Indonesia support (`lang/en.json`, `lang/id.json`).
    - Language Switcher in Navbar and Mobile Menu.
    - User preference saved to database.

## [v1.1.0] - Phase K - 2026-01-10

### Added
- **Multi-Tenancy**:
    - Implemented `TenantScope` for row-level security.
    - Added `company_id` to all core tables.
- **Role-Based Access Control**:
    - Integration with Spatie Permission.
    - Default roles: Admin, Manager, Staff.

### Security
- **Data Isolation**: Enforced Global Scope on all Eloquent models to prevent cross-tenant leakage.

## [v1.0.0] - Initial Release

### Added
- Core Product Management (SKU, Category, Unit).
- Stock Transactions (In, Out, Opname).
- Basic Reporting (Stock Card).
