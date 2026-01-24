# 🏗 AgroWMS Architecture Guide

This document provides a high-level overview of the **AgroWMS** architecture, designed for Scalability, Security, and Maintainability.

## 1. Multi-Tenancy (Phase K)

AgroWMS uses a **Single Database, Logical Isolation** strategy. This is optimal for SaaS applications requiring rapid scaling and unified analytics.

### The `TenantScope`
Isolation is enforced at the **Row Level** using Laravel's Global Scopes.
*   **Mechanism**: Every core model (Product, Warehouse, Transaction) has a `company_id` column.
*   **Enforcement**: The `TenantScope` automatically applies `where('company_id', auth()->user()->company_id)` to *all* queries.
*   **Benefit**: Developers cannot accidentally query data from another tenant.

```php
// User Code:
Product::all();

// Executed SQL:
select * from products where company_id = ? and deleted_at is null
```

## 2. Service Layer Pattern

We avoid "Fat Controllers". Business logic is encapsulated in **Services**.

### Why?
*   **Reusability**: Logic (e.g., "Receive Stock") can be called from a Controller, an API, or a Console Command.
*   **Testing**: Services are easier to unit test than Controllers.
*   **Clarity**: Controllers focus on HTTP (Request/Response), Services focus on Domain Logic.

### Key Key Services
*   `BatchService`: Handles FIFO/FEFO logic, expiry tracking, and batch splits.
*   `PdfService`: Centralized PDF generation using DomPDF with tenant branding.
*   `InventoryReportService`: Complex aggregation queries for dashboards.

## 3. Directory Map

Where does the code live?

```
app/
├── Http/
│   ├── Controllers/       # HTTP Entry Points (Thin)
│   ├── Middleware/        # Auth, Locale, Rate Limiting
│   └── Requests/          # Form Validation
├── Models/
│   ├── Scopes/            # TenantScope lives here
│   └── ...                # Domain Entities (User, Product, etc.)
├── Services/              # ✨ BUSINESS LOGIC CORE ✨
├── Policies/              # Authorization Logic
└── Providers/             # Dependency Injection
```

## 4. Batch Traceability Engine

AgroWMS features a sophisticated traceability engine:
1.  **Inbound**: Stock In -> Batch Created (Lot #123).
2.  **Movement**: Transfer -> New Batch Created (Linked to Parent Batch).
3.  **Outbound**: Sales Order -> Batch Allocated (FIFO).

This creates a **Tree Structure** allowing full audit trails from Supplier to Customer.

## 5. Security & Compliance (UU PDP)

*   **Data Sovereignty**: All tenant data resides in the same region (Configurable).
*   **Logical Separation**: Enforced via `TenantScope`.
*   **Encryption**: Sensitive fields are encrypted at rest.
*   **Audit Logs**: All mutations are recorded in `activity_log` with `user_id` and `IP`.
