# 📊 Database Entity Relationship Diagram

> Complete ERD for **GetLanded** — a multi-tenant warehouse management SaaS.
> All tenant-scoped tables include a `company_id` foreign key.

---

## Master ERD (All Domains)

```mermaid
erDiagram
    %% ==========================================
    %% CORE: Multi-Tenancy & Auth
    %% ==========================================
    
    companies {
        bigint id PK
        string name
        string code
        string email
        string phone
        string address
        string logo_path
        string tax_id
        json subscription_data
        timestamp created_at
    }

    users {
        bigint id PK
        bigint company_id FK
        string name
        string email
        string password
        string role
        string locale
        boolean is_active
        timestamp last_login_at
        string last_login_ip
    }

    roles {
        bigint id PK
        bigint company_id FK
        string name
        string guard_name
    }

    permissions {
        bigint id PK
        string name
        string guard_name
        string module
    }

    companies ||--o{ users : "employs"
    companies ||--o{ roles : "defines"
    roles }o--o{ permissions : "has"
    users }o--o{ roles : "assigned"

    %% ==========================================
    %% PRODUCT CATALOG
    %% ==========================================

    categories {
        bigint id PK
        bigint company_id FK
        string name
    }

    products {
        bigint id PK
        bigint company_id FK
        bigint category_id FK
        string code
        string name
        string description
        string unit
        decimal purchase_price
        decimal selling_price
        decimal weighted_average_cost
        int min_stock
        boolean has_variants
        boolean enable_batch_tracking
        string batch_method
        string hs_code
        string origin_country
        decimal net_weight
        decimal cbm_volume
        boolean status
        timestamp deleted_at
    }

    product_variants {
        bigint id PK
        bigint product_id FK
        string sku
        string name
        string attribute_values
        decimal price_adjustment
        boolean is_active
    }

    product_warehouse {
        bigint product_id FK
        bigint warehouse_id FK
        int stock
        string rack_location
        int min_stock
    }

    categories ||--o{ products : "contains"
    products ||--o{ product_variants : "has"
    products }o--o{ warehouses : "stocked_in"

    %% ==========================================
    %% WAREHOUSE & LOCATION
    %% ==========================================

    warehouses {
        bigint id PK
        bigint company_id FK
        string name
        string code
        string address
        string city
        boolean is_active
        boolean is_default
    }

    warehouse_zones {
        bigint id PK
        bigint warehouse_id FK
        string name
        string code
        string type
    }

    warehouse_racks {
        bigint id PK
        bigint zone_id FK
        string name
        string code
        int capacity
    }

    warehouse_bins {
        bigint id PK
        bigint rack_id FK
        string name
        string code
        string type
        int max_weight
    }

    warehouses ||--o{ warehouse_zones : "divided_into"
    warehouse_zones ||--o{ warehouse_racks : "contains"
    warehouse_racks ||--o{ warehouse_bins : "holds"

    %% ==========================================
    %% BATCH TRACEABILITY
    %% ==========================================

    batches {
        bigint id PK
        bigint company_id FK
        string batch_number
        bigint product_id FK
        bigint variant_id FK
        bigint supplier_id FK
        bigint stock_in_id FK
        date manufacture_date
        date expiry_date
        decimal cost_price
        string status
    }

    stock_locations {
        bigint id PK
        bigint batch_id FK
        bigint bin_id FK
        int quantity
        int reserved_quantity
    }

    batch_movements {
        bigint id PK
        bigint batch_id FK
        string type
        int quantity
        bigint from_bin_id FK
        bigint to_bin_id FK
        bigint reference_id
        string reference_type
    }

    batches ||--o{ stock_locations : "located_at"
    batches ||--o{ batch_movements : "tracked_by"
    stock_locations }o--|| warehouse_bins : "in_bin"
    products ||--o{ batches : "tracked_as"

    %% ==========================================
    %% PROCUREMENT (INBOUND)
    %% ==========================================

    suppliers {
        bigint id PK
        bigint company_id FK
        string name
        string email
        string phone
        string address
        string country
        decimal latitude
        decimal longitude
    }

    purchase_orders {
        bigint id PK
        bigint company_id FK
        string po_number
        bigint supplier_id FK
        bigint warehouse_id FK
        bigint inbound_shipment_id FK
        date order_date
        date expected_delivery_date
        string status
        decimal total_amount
        string currency_code
        decimal exchange_rate_at_transaction
        decimal transaction_fees
        decimal net_amount
        bigint created_by FK
        bigint approved_by FK
    }

    purchase_order_details {
        bigint id PK
        bigint purchase_order_id FK
        bigint product_id FK
        int quantity_ordered
        int quantity_received
        decimal unit_price
        decimal subtotal
    }

    suppliers ||--o{ purchase_orders : "supplies"
    purchase_orders ||--o{ purchase_order_details : "contains"
    purchase_order_details }o--|| products : "for_product"
    purchase_orders }o--|| warehouses : "delivers_to"

    %% ==========================================
    %% STOCK TRANSACTIONS
    %% ==========================================

    stock_ins {
        bigint id PK
        bigint company_id FK
        bigint warehouse_id FK
        bigint supplier_id FK
        bigint purchase_order_id FK
        string reference_number
        date date
        string status
        string approved_by
        timestamp approved_at
        string notes
    }

    stock_in_details {
        bigint id PK
        bigint stock_in_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
    }

    stock_outs {
        bigint id PK
        bigint company_id FK
        bigint warehouse_id FK
        string reference_number
        date date
        string status
        string notes
    }

    stock_out_details {
        bigint id PK
        bigint stock_out_id FK
        bigint product_id FK
        int quantity
    }

    stock_opnames {
        bigint id PK
        bigint company_id FK
        bigint warehouse_id FK
        bigint product_id FK
        int system_stock
        int physical_stock
        int difference
        string status
        string notes
    }

    stock_ins ||--o{ stock_in_details : "contains"
    stock_in_details }o--|| products : "receives"
    stock_outs ||--o{ stock_out_details : "contains"
    stock_out_details }o--|| products : "dispatches"
    warehouses ||--o{ stock_ins : "receives_at"
    warehouses ||--o{ stock_outs : "ships_from"
    suppliers ||--o{ stock_ins : "delivered_by"
    batches }o--o| stock_ins : "created_from"

    %% ==========================================
    %% INTER-WAREHOUSE TRANSFERS
    %% ==========================================

    inter_warehouse_transfers {
        bigint id PK
        bigint company_id FK
        string transfer_number
        bigint from_warehouse_id FK
        bigint to_warehouse_id FK
        date transfer_date
        string status
        string notes
    }

    inter_warehouse_transfer_items {
        bigint id PK
        bigint transfer_id FK
        bigint product_id FK
        int quantity
    }

    inter_warehouse_transfers ||--o{ inter_warehouse_transfer_items : "contains"
    inter_warehouse_transfer_items }o--|| products : "moves"

    %% ==========================================
    %% SALES (OUTBOUND)
    %% ==========================================

    customers {
        bigint id PK
        bigint company_id FK
        string name
        string email
        string phone
        string address
        string country
        decimal credit_balance
    }

    sales_orders {
        bigint id PK
        bigint company_id FK
        string so_number
        bigint customer_id FK
        bigint warehouse_id FK
        bigint stock_out_id FK
        date order_date
        date delivery_date
        string status
        string payment_status
        decimal subtotal
        decimal tax
        decimal discount
        decimal total
        string currency_code
        decimal exchange_rate_at_transaction
        decimal transaction_fees
        decimal net_amount
        decimal amount_paid
        decimal total_bank_fees
        decimal exchange_gain_loss
    }

    sales_order_items {
        bigint id PK
        bigint sales_order_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
        decimal subtotal
        decimal cost_basis
    }

    customers ||--o{ sales_orders : "places"
    sales_orders ||--o{ sales_order_items : "contains"
    sales_order_items }o--|| products : "sells"
    sales_orders }o--|| warehouses : "fulfilled_from"
    sales_orders }o--o| stock_outs : "triggers"

    %% ==========================================
    %% INVOICING & PAYMENTS
    %% ==========================================

    invoices {
        bigint id PK
        bigint company_id FK
        bigint sales_order_id FK
        string invoice_number
        date invoice_date
        date due_date
        decimal total
        string status
    }

    payments {
        bigint id PK
        bigint company_id FK
        bigint sales_order_id FK
        bigint customer_id FK
        bigint bank_account_id FK
        date payment_date
        decimal amount
        decimal bank_fees
        string currency_code
        decimal exchange_rate
        decimal base_currency_amount
        string payment_method
        string reference
    }

    payment_allocations {
        bigint id PK
        bigint payment_id FK
        bigint sales_order_id FK
        decimal amount
    }

    company_bank_accounts {
        bigint id PK
        bigint company_id FK
        string bank_name
        string account_number
        string account_holder
        string currency_code
        boolean is_default
    }

    sales_orders ||--o| invoices : "generates"
    sales_orders ||--o{ payments : "receives"
    payments ||--o{ payment_allocations : "allocated_to"
    payments }o--o| company_bank_accounts : "deposited_to"
    customers ||--o{ payments : "pays"

    %% ==========================================
    %% SUPPLIER PAYMENTS
    %% ==========================================

    supplier_payments {
        bigint id PK
        bigint company_id FK
        bigint supplier_id FK
        bigint purchase_order_id FK
        string payment_type
        string payment_method
        date payment_date
        decimal amount
        string currency_code
        decimal exchange_rate
        string lc_number
        date lc_expiry_date
        string tt_reference
        string status
        boolean is_reconciled
    }

    suppliers ||--o{ supplier_payments : "receives"
    purchase_orders ||--o{ supplier_payments : "paid_via"

    %% ==========================================
    %% SHIPPING & LOGISTICS
    %% ==========================================

    inbound_shipments {
        bigint id PK
        bigint company_id FK
        string shipment_number
        string carrier_name
        string vessel_flight_number
        string origin_port
        string destination_port
        date etd
        date eta
        date actual_arrival_date
        string status
    }

    outbound_shipments {
        bigint id PK
        bigint company_id FK
        bigint sales_order_id FK
        string shipment_number
        date shipment_date
        string carrier_name
        string vessel_name
        string bill_of_lading
        string incoterm
        decimal freight_cost
        decimal insurance_cost
        string status
    }

    containers {
        bigint id PK
        bigint outbound_shipment_id FK
        string container_number
        string container_type
        string seal_number
        decimal gross_weight
        int package_count
    }

    container_items {
        bigint id PK
        bigint container_id FK
        bigint product_id FK
        int quantity
        decimal weight
    }

    shipment_expenses {
        bigint id PK
        bigint company_id FK
        bigint inbound_shipment_id FK
        bigint outbound_shipment_id FK
        string expense_type
        string description
        decimal amount
        string currency_code
        decimal weight
        decimal volume
    }

    inbound_shipments ||--o{ purchase_orders : "consolidates"
    inbound_shipments ||--o{ shipment_expenses : "incurs"
    outbound_shipments ||--o{ containers : "loads"
    outbound_shipments ||--o{ shipment_expenses : "incurs"
    containers ||--o{ container_items : "packs"
    sales_orders ||--o{ outbound_shipments : "shipped_via"

    %% ==========================================
    %% CUSTOMS & TRADE COMPLIANCE
    %% ==========================================

    customs_declarations {
        bigint id PK
        bigint company_id FK
        bigint outbound_shipment_id FK
        string declaration_number
        string declaration_type
        date declaration_date
        string hs_code
        decimal declared_value
        decimal duty_rate
        decimal duty_amount
        decimal vat_rate
        decimal vat_amount
        decimal pph_rate
        decimal pph_amount
        decimal anti_dumping_rate
        decimal anti_dumping_amount
        decimal total_tax
        string fta_scheme
        string status
    }

    customs_declaration_items {
        bigint id PK
        bigint customs_declaration_id FK
        bigint product_id FK
        int quantity
        decimal unit_value
    }

    hs_codes {
        bigint id PK
        string code
        string description
        decimal default_duty_rate
    }

    fta_schemes {
        bigint id PK
        bigint company_id FK
        string name
        string code
    }

    fta_rates {
        bigint id PK
        bigint fta_scheme_id FK
        string hs_code
        decimal preferential_rate
    }

    customs_permits {
        bigint id PK
        bigint company_id FK
        string permit_type
        string permit_number
        date issue_date
        date expiry_date
        string status
    }

    outbound_shipments ||--o| customs_declarations : "declared_by"
    customs_declarations ||--o{ customs_declaration_items : "contains"
    fta_schemes ||--o{ fta_rates : "defines"

    %% ==========================================
    %% CURRENCY
    %% ==========================================

    currencies {
        bigint id PK
        string code
        string name
        string symbol
        decimal exchange_rate
        boolean is_base
        timestamp rate_updated_at
    }

    %% ==========================================
    %% DOCUMENTS & ATTACHMENTS
    %% ==========================================

    documents {
        bigint id PK
        bigint company_id FK
        bigint inbound_shipment_id FK
        string documentable_type
        bigint documentable_id
        string title
        string file_path
        string file_type
        int file_size
        string category
    }

    inbound_shipments ||--o{ documents : "attached"

    %% ==========================================
    %% SYSTEM & AUDIT
    %% ==========================================

    audit_logs {
        bigint id PK
        bigint company_id FK
        bigint user_id FK
        string action
        string auditable_type
        bigint auditable_id
        json old_values
        json new_values
        string ip_address
        string user_agent
    }

    import_jobs {
        bigint id PK
        bigint company_id FK
        string type
        string file_path
        string status
        int total_rows
        int processed_rows
        json column_mapping
        json errors
    }

    settings {
        bigint id PK
        string key
        string value
        string group
    }

    webhooks {
        bigint id PK
        bigint company_id FK
        string url
        string events
        boolean is_active
    }

    webhook_logs {
        bigint id PK
        bigint webhook_id FK
        string event
        int status_code
        text response_body
    }

    users ||--o{ audit_logs : "performed"
    webhooks ||--o{ webhook_logs : "triggered"

    %% ==========================================
    %% SALES RETURNS & CLAIMS
    %% ==========================================

    sales_returns {
        bigint id PK
        bigint company_id FK
        bigint sales_order_id FK
        string return_number
        date return_date
        string reason
        string status
        decimal refund_amount
    }

    sales_return_items {
        bigint id PK
        bigint sales_return_id FK
        bigint product_id FK
        int quantity
        decimal unit_price
    }

    claims {
        bigint id PK
        bigint company_id FK
        string claim_number
        string claimable_type
        bigint claimable_id
        string type
        decimal amount
        string status
    }

    claim_evidences {
        bigint id PK
        bigint claim_id FK
        string file_path
        string description
    }

    sales_orders ||--o{ sales_returns : "returned_via"
    sales_returns ||--o{ sales_return_items : "contains"
    claims ||--o{ claim_evidences : "supported_by"

    %% ==========================================
    %% UOM CONVERSIONS
    %% ==========================================

    uom_conversions {
        bigint id PK
        bigint company_id FK
        bigint product_id FK
        string from_unit
        string to_unit
        decimal conversion_factor
    }

    products ||--o{ uom_conversions : "converts"
```

---

## Table Count by Domain

| Domain | Tables | Key Models |
|--------|--------|------------|
| **Auth & Tenancy** | 4 | Company, User, Role, Permission |
| **Product Catalog** | 3 | Product, ProductVariant, Category |
| **Warehouse** | 5 | Warehouse, Zone, Rack, Bin, ProductWarehouse |
| **Batch Traceability** | 3 | Batch, StockLocation, BatchMovement |
| **Procurement** | 4 | Supplier, PurchaseOrder, PurchaseOrderDetail, SupplierPayment |
| **Stock Transactions** | 6 | StockIn/Out, StockInDetail/OutDetail, StockOpname, Transfer |
| **Sales** | 5 | Customer, SalesOrder, SalesOrderItem, SalesReturn, SalesReturnItem |
| **Finance** | 5 | Invoice, Payment, PaymentAllocation, CompanyBankAccount, Currency |
| **Shipping** | 4 | InboundShipment, OutboundShipment, Container, ShipmentExpense |
| **Customs & Trade** | 5 | CustomsDeclaration, CustomsDeclarationItem, HsCode, FtaScheme, FtaRate |
| **System** | 7 | AuditLog, ImportJob, Document, Setting, Webhook, WebhookLog, Claim |
| **Total** | **~51** | |
