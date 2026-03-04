# 🏗️ System Architecture Overview

> **GetLanded** is a multi-tenant SaaS platform for import/export warehouse management,
> built with **Laravel 11**, **Blade/Vite**, and deployed via **Docker + Coolify**.

---

## High-Level Architecture

```mermaid
graph TB
    subgraph Client["🖥️ Client Layer"]
        Browser["Web Browser"]
        API["REST API Client"]
    end

    subgraph CDN["☁️ CDN / Edge"]
        CF["Cloudflare"]
    end

    subgraph App["🐳 Docker — App Container"]
        Nginx["Nginx Reverse Proxy"]
        PHP["PHP 8.4 FPM"]
        
        subgraph Laravel["Laravel 11 Application"]
            Middleware["Middleware Pipeline"]
            Controllers["Controllers (54)"]
            Services["Service Layer (27)"]
            Models["Eloquent Models (62)"]
            Policies["Authorization Policies"]
        end
    end

    subgraph Worker["🐳 Docker — Worker Container"]
        QueueWorker["Queue Worker"]
        Jobs["Background Jobs"]
    end

    subgraph Storage["💾 Data Layer"]
        DB[("PostgreSQL / MySQL")]
        R2["Cloudflare R2\n(File Storage)"]
        Cache["Redis / File Cache"]
    end

    subgraph External["🌐 External APIs"]
        CurrencyAPI["AwesomeAPI\n(Exchange Rates)"]
        Telegram["Telegram Bot\n(Notifications)"]
        WhatsApp["WhatsApp API\n(Notifications)"]
    end

    Browser --> CF --> Nginx --> PHP
    API --> CF --> Nginx --> PHP
    PHP --> Laravel
    Middleware --> Controllers --> Services --> Models --> DB
    Services --> R2
    Services --> Cache
    QueueWorker --> Jobs --> Services
    Jobs --> DB
    Services --> CurrencyAPI
    Services --> Telegram
    Services --> WhatsApp
```

---

## Request Lifecycle

```mermaid
sequenceDiagram
    participant Browser
    participant Nginx
    participant Middleware
    participant Controller
    participant Service
    participant Model
    participant DB

    Browser->>Nginx: HTTP Request
    Nginx->>Middleware: Forward to PHP-FPM
    
    Note over Middleware: SecurityHeaders
    Note over Middleware: BlockSuspiciousRequests
    Note over Middleware: Authenticate
    Note over Middleware: SetLocale
    Note over Middleware: CheckPermission
    Note over Middleware: TenantScope (auto-applied)
    
    Middleware->>Controller: Authorized Request
    Controller->>Service: Business Logic
    Service->>Model: Data Access
    Model->>DB: Query (WHERE company_id = ?)
    DB-->>Model: Filtered Results
    Model-->>Service: Eloquent Collection
    Service-->>Controller: Processed Data
    Controller-->>Browser: Blade View / JSON
```

---

## Service Layer Map

| Service | Responsibility |
|---------|---------------|
| `ImportService` | Smart CSV/Excel import with fuzzy matching, chunking, S3 support |
| `BatchService` | FIFO/LIFO/FEFO batch allocation and tracking |
| `BatchAllocationService` | Allocate batches to outbound orders |
| `BatchInboundService` | Create batches from stock-in transactions |
| `LandedCostService` | Calculate landed cost per unit across shipment expenses |
| `DutyCalculationService` | Indonesian customs duty calculation (BM, PPN, PPh, Anti-Dumping) |
| `CurrencyService` | Real-time exchange rate sync via AwesomeAPI |
| `StockTransactionFinalizer` | Finalize stock-in/out with approval workflow |
| `SoftInventoryService` | Soft reservation of stock for pending orders |
| `PickingListService` | Generate warehouse picking lists for fulfillment |
| `PdfService` | Invoice, packing list, and document PDF generation |
| `DocumentService` | File attachment management for shipments |
| `UomConversionService` | Unit of Measure conversion (kg↔lbs, pcs↔dozen, etc.) |
| `InventoryReportService` | Dashboard aggregation and analytics |
| `TrackingService` | Shipment tracking number management |
| `AlertService` | Low stock, expiry, and overdue alerts |
| `GlobalSearchService` | Cross-entity search (products, orders, customers) |
| `GeocodingService` | Supplier address geocoding |
| `WebhookService` | Outbound webhook event dispatch |
| `TelegramService` | Telegram notification delivery |
| `WhatsappService` | WhatsApp notification delivery |
| `SelfApprovalGuard` | Prevent users from approving their own transactions |
| `HolidayService` | Business day calculation for delivery estimates |
| `BackupService` | Database backup management |
| `NotificationThrottleService` | Rate-limit notifications per user |

---

## Middleware Pipeline

```mermaid
graph LR
    A["🔒 SecurityHeaders"] --> B["🛡️ BlockSuspiciousRequests"]
    B --> C["🔑 Authenticate"]
    C --> D["🌍 SetLocale"]
    D --> E["👤 LogUserActivity"]
    E --> F["🏢 TenantScope\n(auto via BelongsToTenant)"]
    F --> G["🔐 CheckPermission"]
    G --> H["📋 Controller"]
```

---

## Deployment Architecture (Coolify)

```mermaid
graph TB
    subgraph Coolify["Coolify PaaS"]
        subgraph AppService["getlanded-prod"]
            AppContainer["App Container\n(Nginx + PHP-FPM)"]
        end
        subgraph WorkerService["getlanded-worker"]
            WorkerContainer["Worker Container\n(php artisan queue:work)"]
        end
    end

    subgraph Infra["Infrastructure"]
        PG[("PostgreSQL")]
        R2["Cloudflare R2\n(S3-compatible)"]
    end

    AppContainer -->|"Read/Write"| PG
    WorkerContainer -->|"Read/Write"| PG
    AppContainer -->|"Upload Files"| R2
    WorkerContainer -->|"Download Files\n(temp for processing)"| R2
    
    Internet["🌐 Internet"] --> CF["Cloudflare DNS/CDN"]
    CF --> AppContainer
```
