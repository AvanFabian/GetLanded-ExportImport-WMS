# 🔄 Business Flow Diagrams

> End-to-end workflows for the core business processes in **GetLanded**.

---

## 1. Procurement Flow (Inbound)

```mermaid
flowchart TD
    A["📋 Create Purchase Order\n(PO-20260301-0001)"] --> B{"Status: Draft"}
    B --> C["Submit for Approval"]
    C --> D{"Manager Reviews"}
    D -->|Approve| E["Status: Approved"]
    D -->|Reject| F["Status: Rejected"]
    
    E --> G["Link to Inbound Shipment\n(SHP-202603-0001)"]
    G --> H["Track Vessel/Flight"]
    H --> I["Shipment Arrives"]
    
    I --> J["📦 Create Stock In\n(Goods Receipt)"]
    J --> K{"Full or Partial?"}
    K -->|Full| L["PO → Received"]
    K -->|Partial| M["PO → Partially Received"]
    M --> J
    
    J --> N["🏷️ Batch Created\n(Lot # auto-generated)"]
    N --> O["📍 Assign to Bin\n(Zone > Rack > Bin)"]
    O --> P["✅ Stock Updated\n(product_warehouse.stock += qty)"]
    
    subgraph LandedCost["💰 Landed Cost Calculation"]
        G --> LC1["Add Shipment Expenses\n(Freight, Insurance, THC)"]
        LC1 --> LC2["LandedCostService\nallocates per unit"]
        LC2 --> LC3["WAC Updated\n(Weighted Average Cost)"]
    end

    subgraph Payment["💳 Supplier Payment"]
        E --> SP1["Create Supplier Payment"]
        SP1 --> SP2{"Payment Type"}
        SP2 -->|TT| SP3["Telegraphic Transfer"]
        SP2 -->|LC| SP4["Letter of Credit"]
        SP3 --> SP5["Reconcile Payment"]
        SP4 --> SP5
    end
```

---

## 2. Sales Flow (Outbound)

```mermaid
flowchart TD
    A["📋 Create Sales Order\n(SO-20260301-00001)"] --> B{"Status: Draft"}
    B --> C["Confirm Order"]
    C --> D["Status: Confirmed"]
    
    D --> E["📦 Create Stock Out\n(Fulfillment)"]
    E --> F["BatchAllocationService\nFIFO/LIFO/FEFO"]
    F --> G["Batches Allocated\n(Reserved from bins)"]
    G --> H["PickingListService\nGenerate Picking List"]
    
    H --> I["Status: Processing"]
    
    I --> J{"Ship Goods?"}
    J -->|Yes| K["Create Outbound Shipment"]
    K --> L["Pack Containers"]
    L --> M["Status: Shipped"]
    
    M --> N["Status: Delivered"]
    
    subgraph Invoicing["🧾 Invoicing"]
        D --> INV1["Generate Invoice\n(PdfService)"]
        INV1 --> INV2["Invoice PDF\nwith company branding"]
    end
    
    subgraph Payments["💳 Customer Payment"]
        N --> PAY1["Record Payment"]
        PAY1 --> PAY2{"Payment Status"}
        PAY2 -->|Full| PAY3["SO → Paid"]
        PAY2 -->|Partial| PAY4["SO → Partial"]
        PAY2 -->|None| PAY5["SO → Unpaid"]
        PAY1 --> PAY6["Exchange Gain/Loss\nCalculation"]
    end

    subgraph Returns["📎 Returns"]
        N --> RET1["Sales Return\n(if needed)"]
        RET1 --> RET2["Stock Reversed"]
        RET2 --> RET3["Refund Issued"]
    end
```

---

## 3. Sales Order State Machine

```mermaid
stateDiagram-v2
    [*] --> draft
    draft --> confirmed : Confirm Order
    confirmed --> processing : Start Fulfillment
    processing --> shipped : Ship Goods
    shipped --> delivered : Confirm Delivery
    confirmed --> cancelled : Cancel
    processing --> cancelled : Cancel
    
    delivered --> [*]
    cancelled --> [*]

    state "Payment Status" as ps {
        [*] --> unpaid
        unpaid --> partial : Partial Payment
        partial --> paid : Full Payment
        unpaid --> paid : Full Payment
        unpaid --> overdue : Past Due Date
    }
```

---

## 4. Purchase Order State Machine

```mermaid
stateDiagram-v2
    [*] --> draft
    draft --> pending : Submit
    pending --> approved : Manager Approves
    pending --> rejected : Manager Rejects
    approved --> partially_received : Partial Receipt
    partially_received --> received : Full Receipt
    approved --> received : Full Receipt
    approved --> cancelled : Cancel
    
    received --> [*]
    rejected --> [*]
    cancelled --> [*]
```

---

## 5. Batch Traceability Flow

```mermaid
flowchart LR
    subgraph Inbound["📥 Inbound"]
        SI["Stock In"] --> B["Batch Created\n(LOT-20260301-001)"]
    end
    
    subgraph Storage["🏭 Warehouse"]
        B --> SL["Stock Location\n(Zone A > Rack 1 > Bin 3)"]
        SL --> BM1["Batch Movement\n(type: received)"]
    end
    
    subgraph Transfer["🔄 Transfer"]
        SL --> T["Inter-Warehouse Transfer"]
        T --> SL2["New Location\n(Warehouse B)"]
        T --> BM2["Batch Movement\n(type: transfer)"]
    end
    
    subgraph Outbound["📤 Outbound"]
        SL --> SO["Sales Order"]
        SO --> BA["Batch Allocation\n(FIFO)"]
        BA --> BM3["Batch Movement\n(type: sold)"]
    end
    
    subgraph Audit["📋 Full Traceability"]
        BM1 --> TRACE["Complete Audit Trail\nSupplier → Warehouse → Customer"]
        BM2 --> TRACE
        BM3 --> TRACE
    end
```

---

## 6. Customs & Export Compliance Flow

```mermaid
flowchart TD
    SO["Sales Order\n(International)"] --> OS["Create Outbound Shipment"]
    OS --> CD["Create Customs Declaration"]
    
    CD --> HS["Assign HS Code"]
    HS --> FTA{"FTA Scheme\nApplicable?"}
    FTA -->|Yes| PR["Apply Preferential Rate\n(from fta_rates table)"]
    FTA -->|No| MFN["Apply MFN Rate"]
    
    PR --> CALC["DutyCalculationService"]
    MFN --> CALC
    
    CALC --> TAX["Calculate Taxes"]
    
    subgraph TaxBreakdown["Tax Breakdown"]
        TAX --> BM["Import Duty (BM)"]
        TAX --> PPN["VAT (PPN) 11%"]
        TAX --> PPH["Income Tax (PPh)"]
        TAX --> AD["Anti-Dumping Duty"]
        TAX --> EX["Excise"]
    end
    
    BM --> TOTAL["Total Tax Payable"]
    PPN --> TOTAL
    PPH --> TOTAL
    AD --> TOTAL
    EX --> TOTAL
    
    TOTAL --> STATUS{"Declaration Status"}
    STATUS --> SUBMIT["Submitted"]
    SUBMIT --> ASSESS["Assessed"]
    ASSESS --> PAID["Paid"]
    PAID --> CLEARED["Cleared ✅"]
```

---

## 7. Warehouse Location Hierarchy

```mermaid
graph TD
    W["🏭 Warehouse\n(e.g. Jakarta Main)"] --> Z1["📦 Zone A\n(Receiving)"]
    W --> Z2["📦 Zone B\n(Storage)"]
    W --> Z3["📦 Zone C\n(Shipping)"]
    
    Z2 --> R1["🗄️ Rack B-01"]
    Z2 --> R2["🗄️ Rack B-02"]
    
    R1 --> B1["📍 Bin B-01-A\n(max 500kg)"]
    R1 --> B2["📍 Bin B-01-B\n(max 500kg)"]
    R1 --> B3["📍 Bin B-01-C\n(max 500kg)"]
    
    B1 --> SL1["Batch LOT-001\nQty: 100 pcs"]
    B1 --> SL2["Batch LOT-002\nQty: 50 pcs"]
    B2 --> SL3["Batch LOT-003\nQty: 200 pcs"]
```

---

## 8. Import (CSV/Excel) Processing Flow

```mermaid
flowchart TD
    A["📤 User Uploads File\n(.csv or .xlsx)"] --> B["ProductController\nStores to S3/R2"]
    B --> C["ImportService.parseFile()\nPreview 5 rows + count"]
    
    C --> D["User Maps Columns\n(Fuzzy auto-match)"]
    D --> E["Dispatch ProcessImportJob\n(Queue)"]
    
    E --> F["Worker picks up job"]
    F --> G["getLocalFilePath()\nDownload from S3 to /tmp"]
    
    G --> H{"File Type"}
    H -->|CSV| I["parseCsv()\nLeague CSV Reader"]
    H -->|XLSX| J["processExcel()\nMaatwebsite Excel\n(500-row chunks)"]
    
    I --> K["Data Cleaning\n(currency, weight, UoM)"]
    J --> K
    
    K --> L["Upsert Products\n(code as unique key)"]
    L --> M["Attach to Warehouses\n(pivot table)"]
    M --> N["Update ImportJob\n(processed_rows++)"]
    
    N --> O{"All rows done?"}
    O -->|Yes| P["Status: Completed ✅"]
    O -->|No| N
    
    G --> CLEAN["Cleanup temp file"]
```

---

## 9. Currency Exchange Rate Sync

```mermaid
sequenceDiagram
    participant Scheduler as Laravel Scheduler
    participant Service as CurrencyService
    participant API as AwesomeAPI
    participant DB as Database
    participant Cache as Cache

    Scheduler->>Service: fetchLatestRates()
    Service->>API: GET /json/last/USD-IDR,USD-EUR,...
    
    alt API Success
        API-->>Service: JSON rates
        Service->>Service: Cross-rate calculation
        Note over Service: EUR-IDR = (USD-IDR) / (USD-EUR)
        Service->>DB: Update currencies table
        Service->>Cache: Cache rates (forever key)
        Service-->>Scheduler: true
    else API Failure
        API-->>Service: Error / Timeout
        Service->>Cache: Read last known rates
        Service->>DB: Update from cache
        Service-->>Scheduler: false (degraded)
    end
```

---

## 10. Approval Workflow

```mermaid
flowchart TD
    A["User Creates Transaction\n(Stock In/Out, PO, Transfer)"] --> B{"Approval Required?\n(Company Policy)"}
    
    B -->|No| C["Auto-Approved\nStatus: Approved"]
    B -->|Yes| D["Status: Pending Approval"]
    
    D --> E["SelfApprovalGuard\nCheck"]
    E --> F{"Same user\nas creator?"}
    F -->|Yes| G["❌ Blocked\nCannot self-approve"]
    F -->|No| H{"User has\napproval permission?"}
    
    H -->|No| I["❌ 403 Forbidden"]
    H -->|Yes| J["✅ Approved"]
    
    J --> K["StockTransactionFinalizer\nExecute transaction"]
    K --> L["Update stock levels"]
    L --> M["Create audit log entry"]
```
