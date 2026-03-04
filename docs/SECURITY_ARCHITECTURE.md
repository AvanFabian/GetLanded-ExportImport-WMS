# 🔐 Security & Multi-Tenancy Architecture

> Security model and data isolation strategy for **GetLanded** SaaS.

---

## Multi-Tenancy Model

GetLanded uses **Single Database, Row-Level Isolation** — the most scalable SaaS pattern.

```mermaid
graph TB
    subgraph DB["Single PostgreSQL Database"]
        subgraph tables["All Tables"]
            P["products\n(company_id = 1)\n(company_id = 2)"]
            W["warehouses\n(company_id = 1)\n(company_id = 2)"]
        end
    end

    subgraph T1["🏢 Tenant 1 (Company A)"]
        U1["User A1"]
        U2["User A2"]
    end

    subgraph T2["🏢 Tenant 2 (Company B)"]
        U3["User B1"]
        U4["User B2"]
    end

    U1 -->|"WHERE company_id = 1"| P
    U2 -->|"WHERE company_id = 1"| P
    U3 -->|"WHERE company_id = 2"| P
    U4 -->|"WHERE company_id = 2"| P
```

### How It Works

```mermaid
sequenceDiagram
    participant User
    participant Auth as Authentication
    participant Scope as TenantScope
    participant Query as Eloquent Query
    participant DB as Database

    User->>Auth: Login (email, password)
    Auth->>Auth: Resolve company_id from User
    Auth-->>User: Session (user.company_id = 1)
    
    User->>Query: Product::all()
    Query->>Scope: TenantScope::apply()
    Note over Scope: Adds WHERE company_id = 1
    Scope->>DB: SELECT * FROM products WHERE company_id = 1
    DB-->>User: Only Tenant 1 products
```

### Key Components

| Component | File | Purpose |
|-----------|------|---------|
| `TenantScope` | `app/Models/Scopes/TenantScope.php` | Global scope — auto-adds `WHERE company_id = ?` |
| `BelongsToTenant` | `app/Models/Traits/BelongsToTenant.php` | Trait — applies scope + auto-sets `company_id` on create |

### Tenant-Scoped Models (27+)

| Domain | Models |
|--------|--------|
| **Catalog** | Product, Category, ProductVariant |
| **Warehouse** | Warehouse, WarehouseZone, WarehouseRack, WarehouseBin |
| **Stock** | StockIn, StockOut, StockOpname, Batch, StockLocation |
| **Procurement** | Supplier, PurchaseOrder, SupplierPayment |
| **Sales** | Customer, SalesOrder, Invoice, Payment |
| **Shipping** | InboundShipment, OutboundShipment, ShipmentExpense |
| **Customs** | CustomsDeclaration, CustomsPermit, FtaScheme |
| **System** | Document, AuditLog, ImportJob, Webhook |

---

## Authorization Architecture

```mermaid
flowchart TD
    REQ["🌐 HTTP Request"] --> MW1["🔒 SecurityHeaders\n(CSP, HSTS, X-Frame)"]
    MW1 --> MW2["🛡️ BlockSuspiciousRequests\n(Path traversal, SQL injection)"]
    MW2 --> MW3["🔑 Authentication\n(Laravel Sanctum/Session)"]
    MW3 --> MW4["🏢 TenantScope\n(Row-level isolation)"]
    MW4 --> MW5["🔐 CheckPermission\n(RBAC Gate)"]
    MW5 --> MW6["📋 Policy Check\n(Model-level auth)"]
    MW6 --> CTRL["✅ Controller\n(Authorized Action)"]
    
    style MW1 fill:#ff6b6b,color:#fff
    style MW2 fill:#ee5a24,color:#fff
    style MW3 fill:#f9ca24,color:#000
    style MW4 fill:#6ab04c,color:#fff
    style MW5 fill:#22a6b3,color:#fff
    style MW6 fill:#4834d4,color:#fff
```

### Two-Layer Authorization

```
Layer 1: TenantScope (Data Filtering)
├── Automatic — applied to ALL queries
├── Prevents cross-tenant data access
└── Cannot be bypassed by regular users

Layer 2: RBAC + Policies (Action Authorization)
├── Role-based — assigns permissions to roles
├── Policy-based — model-level create/update/delete
└── Gate-based — custom permission checks
```

### Permission Matrix

| Permission | Admin | Manager | Staff | Viewer |
|-----------|:-----:|:-------:|:-----:|:------:|
| `stock.in.create` | ✅ | ✅ | ✅ | ❌ |
| `stock.out.create` | ✅ | ✅ | ✅ | ❌ |
| `stock.adjustment` | ✅ | ✅ | ❌ | ❌ |
| `transaction.approve` | ✅ | ✅ | ❌ | ❌ |
| `transaction.reject` | ✅ | ✅ | ❌ | ❌ |
| `finance.view` | ✅ | ✅ | ❌ | ❌ |
| `currency.manage` | ✅ | ❌ | ❌ | ❌ |
| `user.manage` | ✅ | ❌ | ❌ | ❌ |
| `role.manage` | ✅ | ❌ | ❌ | ❌ |
| `report.view` | ✅ | ✅ | ✅ | ✅ |
| `report.export` | ✅ | ✅ | ❌ | ❌ |
| `sales.view` | ✅ | ✅ | ✅ | ❌ |
| `invoice.view` | ✅ | ✅ | ❌ | ❌ |

---

## Security Controls

### Request Security

```mermaid
flowchart LR
    subgraph BlockSuspiciousRequests
        A["Path Traversal\n(../)"] --> BLOCK["❌ 403"]
        B["SQL Injection\n(UNION SELECT)"] --> BLOCK
        C["XSS Patterns\n(<script>)"] --> BLOCK
        D["Shell Injection\n(; rm -rf)"] --> BLOCK
    end

    subgraph SecurityHeaders
        H1["Content-Security-Policy"]
        H2["Strict-Transport-Security"]
        H3["X-Frame-Options: DENY"]
        H4["X-Content-Type-Options: nosniff"]
        H5["Referrer-Policy: strict-origin"]
    end
```

### Self-Approval Prevention

```mermaid
flowchart TD
    A["User creates Stock In\n(created_by = user_id: 5)"] --> B["Submit for Approval"]
    B --> C["Manager Clicks Approve"]
    C --> D{"SelfApprovalGuard\nCheck"}
    D -->|"approver_id == creator_id"| E["❌ Blocked\nCannot approve own transaction"]
    D -->|"approver_id != creator_id"| F["✅ Approved"]
```

### Audit Trail

```mermaid
flowchart LR
    A["Any Data Mutation"] --> B["LogsActivity Trait"]
    B --> C["audit_logs Table"]
    C --> D["Stored Fields"]
    
    subgraph AuditFields["Audit Record"]
        D --> F1["user_id"]
        D --> F2["action (create/update/delete)"]
        D --> F3["old_values (JSON)"]
        D --> F4["new_values (JSON)"]
        D --> F5["ip_address"]
        D --> F6["user_agent"]
        D --> F7["timestamp"]
    end
```

### Security Log

```mermaid
flowchart TD
    A["Suspicious Activity"] --> B["SecurityLog Model"]
    B --> C["Events Tracked"]
    
    C --> D1["Failed Login Attempts"]
    C --> D2["Password Changes"]
    C --> D3["Role Changes"]
    C --> D4["Permission Escalation"]
    C --> D5["Blocked Requests"]
    C --> D6["Rate Limit Hits"]
```

---

## Data Protection (UU PDP Compliance)

| Requirement | Implementation |
|------------|---------------|
| **Data Sovereignty** | Configurable database region |
| **Logical Separation** | TenantScope (row-level) |
| **Encryption at Rest** | Database-level encryption |
| **Audit Logs** | All mutations recorded with IP + user agent |
| **Access Control** | RBAC with granular permissions |
| **Data Minimization** | Soft deletes preserve data integrity |
| **Consent** | User registration requires explicit consent |
