# 📋 Complete Inventory System - End-to-End Testing Guide

**Date:** November 11, 2025  
**Feature:** Full Warehouse Inventory Management System  
**Architecture:** Multi-Warehouse with Pivot Table (Product ↔ Warehouse)  
**Status:** Ready for Comprehensive Testing

---

## 🏗️ System Architecture Overview

### **Multi-Warehouse Model**
This system uses **industry-standard many-to-many relationship** between Products and Warehouses:

```
Product (ELC-001)
    ↓
product_warehouse (pivot table)
    ├─ Warehouse A → Stock: 15 units, Rack: A-01-01
    ├─ Warehouse B → Stock: 20 units, Rack: B-02-03
    └─ Warehouse C → Stock: 10 units, Rack: C-05-12
```

**Key Features:**
- ✅ One product can exist in multiple warehouses
- ✅ Each warehouse tracks stock independently
- ✅ Stock movements are warehouse-specific
- ✅ Supports inter-warehouse transfers
- ✅ Prevents duplicate product codes

---

## ✅ Pre-Testing Setup

### 1. Database Preparation
```bash
# Fresh database with ONLY users and warehouses (no master data)
php artisan migrate:fresh --seed

# This will create:
# - 4 Primary Users: owner@avandigital.id, manager@avandigital.id, staff@avandigital.id, viewer@avandigital.id
# - 2 Warehouses: Main Warehouse (Jakarta), Secondary Warehouse (Surabaya)
# - NO categories, suppliers, or products (you'll create them manually)
```

### 2. Login Credentials
```
Email: owner@avandigital.id
Password: demo1234
Role: Admin (full access)
```

### 3. What to Test
We'll test the **COMPLETE inventory workflow** in this order:
1. **Master Data Setup** (Categories, Suppliers, Products)
2. **Stock Management** (Stock In, Stock Out, Stock Opname)
3. **Inter-Warehouse Transfers**
4. **Sales Cycle** (Customers, Sales Orders, Invoices)
5. **Reports & Dashboard**

---

---

## 🧪 EXTRA: Stress Testing (High Volume)

### **Objective: Validate 25,000 Record Import**

**1. Generate Data**
Run the generator script to create a 4MB+ XLSX file with 25,000 realistic records:
```bash
python generate_stress_test.py
```
*File created: `public/stress_test_products.xlsx`*

**2. Run Import**
1. Go to **Products**
2. Click **Import**
3. Select `public/stress_test_products.xlsx`
4. Click **Upload**

**Expected Results:**
- ✅ Process completes in ~60-120 seconds.
- ✅ RAM usage stays steady (Chunk Reading active).
- ✅ 25,000 new products appear.

---

## 📦 PHASE 1: Master Data Setup

### **STEP 1.1: Create Categories** 🏷️

**Path:** Dashboard → Master Data → Kategori → + Tambah Kategori

**Test Data (Create 3 categories):**
```
1. Electronics
   - Description: Electronic devices and computer equipment
   - Status: ✓ Active

2. Furniture
   - Description: Office and home furniture items
   - Status: ✓ Active

3. Stationery
   - Description: Office supplies and consumables
   - Status: ✓ Active
```

**Expected Result:**
- ✅ Each category created successfully
- ✅ Categories appear in dropdown when creating products
- ✅ Can search and filter categories

**Test Cases:**
- [ ] ✅ Create category with all fields
- [ ] ❌ Try creating without name (should show validation error)
- [ ] ✅ Edit category name and description
- [ ] ✅ Deactivate category (should not appear in product form)
- [ ] ✅ Reactivate category
- [ ] ❌ Try deleting category with products (should show error)
- [ ] ✅ Delete category without products

---

### **STEP 1.2: Create Suppliers** 🏢

**Path:** Dashboard → Master Data → Supplier → + Tambah Supplier

**Test Data (Create 2 suppliers):**
```
1. PT. Elektronik Jaya
   - Address: Jl. Industri No. 123, Jakarta Selatan 12190
   - Phone: 021-12345678
   - Email: sales@elektronikjaya.com
   - Contact Person: Budi Santoso

2. CV. Furniture Indo
   - Address: Jl. Mebel Raya No. 45, Surabaya 60123
   - Phone: 031-87654321
   - Email: info@furnitureindo.co.id
   - Contact Person: Siti Rahayu
```

**Expected Result:**
- ✅ Both suppliers created successfully
- ✅ Suppliers appear in dropdown when creating stock in
- ✅ Contact info stored correctly

**Test Cases:**
- [ ] ✅ Create supplier with all fields
- [ ] ❌ Try creating without name (validation error)
- [ ] ❌ Try creating without phone (validation error)
- [ ] ✅ Create supplier without email (email is optional)
- [ ] ✅ Edit supplier information
- [ ] ✅ Search suppliers by name/phone/email
- [ ] ❌ Try deleting supplier with stock ins (should show error)
- [ ] ✅ Delete supplier without transactions

---

### **STEP 1.3: Create Products (Multi-Warehouse)** 📱

**Path:** Dashboard → Master Data → Produk → + Tambah Produk

**⚠️ IMPORTANT: Multi-Warehouse Behavior**
- You'll select **ONE warehouse** when creating product
- Product will be assigned to that warehouse with initial stock = 0
- Later, you can add the same product to OTHER warehouses via Stock In
- Product code must be UNIQUE across ALL warehouses

**Test Data (Create 5 products):**

```
1. Laptop Dell Inspiron 15
   - Code: ELC-001 (unique, will be used in all warehouses)
   - Category: Electronics
   - Unit: pcs
   - Min Stock: 5
   - Purchase Price: 8,000,000
   - Selling Price: 9,500,000
   - Warehouse: Main Warehouse (Jakarta)
   - Stock: 0 (we'll add stock via Stock In)
   - Rack Location: A-01-01
   - Status: ✓ Active
   - Description: Dell Inspiron 15 3000 Series, Core i5, 8GB RAM

2. Mouse Wireless Logitech
   - Code: ELC-002
   - Category: Electronics
   - Unit: pcs
   - Min Stock: 15
   - Purchase Price: 150,000
   - Selling Price: 200,000
   - Warehouse: Main Warehouse (Jakarta)
   - Stock: 0
   - Rack Location: A-01-02
   - Status: ✓ Active

3. Office Chair Ergonomic
   - Code: FRN-001
   - Category: Furniture
   - Unit: pcs
   - Min Stock: 10
   - Purchase Price: 1,500,000
   - Selling Price: 1,800,000
   - Warehouse: Secondary Warehouse (Surabaya)
   - Stock: 0
   - Rack Location: B-02-05
   - Status: ✓ Active

4. Ballpoint Pen Blue (Box of 50)
   - Code: STN-001
   - Category: Stationery
   - Unit: box
   - Min Stock: 20
   - Purchase Price: 50,000
   - Selling Price: 65,000
   - Warehouse: Main Warehouse (Jakarta)
   - Stock: 0
   - Rack Location: C-03-10
   - Status: ✓ Active

5. Paper A4 80gsm (Ream)
   - Code: STN-002
   - Category: Stationery
   - Unit: ream
   - Min Stock: 30
   - Purchase Price: 35,000
   - Selling Price: 45,000
   - Warehouse: Main Warehouse (Jakarta)
   - Stock: 0
   - Rack Location: C-03-15
   - Status: ✓ Active
```

**Expected Result:**
- ✅ Product created with selected warehouse assignment
- ✅ Record appears in `product_warehouse` pivot table
- ✅ Product detail page shows warehouse-specific stock
- ✅ Product code is globally unique

**Test Cases:**
- [ ] ✅ Create product and assign to Main Warehouse
- [ ] ✅ Create product and assign to Secondary Warehouse
- [ ] ❌ Try creating product with duplicate code (should show error: "Code already exists")
- [ ] ❌ Try creating without required fields (validation errors)
- [ ] ✅ Product appears in selected warehouse's product list
- [ ] ✅ Product detail shows: "Stock in Main Warehouse: 0 pcs, Rack: A-01-01"
- [ ] ✅ Edit product info (name, price, min_stock)
- [ ] ✅ Search products by code/name
- [ ] ✅ Filter by category
- [ ] ✅ Filter by warehouse
- [ ] ✅ Filter by status (active/inactive)

**⚠️ Critical Test: Prevent Duplicate Codes**
- [ ] ❌ Try creating "ELC-001" in Secondary Warehouse → Should show error
- [ ] ✅ Verify product codes are globally unique, not per-warehouse

---

## 📥 PHASE 2: Stock Management

### **STEP 2.1: Stock In - Add Initial Inventory** 📦

**Path:** Dashboard → Transaksi → Stok Masuk → + Tambah Stok Masuk

**Purpose:** Add products to inventory (from supplier purchases)

**Test Data (Create 3 Stock Ins):**

```
Stock In #1: Main Warehouse - Electronics
----------------------------------------------
Warehouse: Main Warehouse (Jakarta)
Supplier: PT. Elektronik Jaya
Reference: PO-2025-001
Date: [Today's date]
Notes: Initial stock for store opening

Items:
  1. ELC-001 (Laptop Dell) - Qty: 20
  2. ELC-002 (Mouse Logitech) - Qty: 50

Expected Stock After:
  - ELC-001 in Main WH: 0 + 20 = 20 units
  - ELC-002 in Main WH: 0 + 50 = 50 units
```

```
Stock In #2: Main Warehouse - Stationery
----------------------------------------------
Warehouse: Main Warehouse (Jakarta)
Supplier: CV. Furniture Indo
Reference: PO-2025-002
Date: [Today's date]

Items:
  1. STN-001 (Pen Box) - Qty: 100
  2. STN-002 (Paper A4) - Qty: 150

Expected Stock After:
  - STN-001 in Main WH: 0 + 100 = 100 boxes
  - STN-002 in Main WH: 0 + 150 = 150 reams
```

```
Stock In #3: Secondary Warehouse - Furniture
----------------------------------------------
Warehouse: Secondary Warehouse (Surabaya)
Supplier: CV. Furniture Indo
Reference: PO-2025-003
Date: [Today's date]

Items:
  1. FRN-001 (Office Chair) - Qty: 30

Expected Stock After:
  - FRN-001 in Secondary WH: 0 + 30 = 30 units
```

**Expected Result:**
- ✅ Auto-generates Stock In number (STK-IN-YYYYMMDD-00001)
- ✅ Stock increases in `product_warehouse` pivot table
- ✅ Each product's stock tracked per warehouse
- ✅ Can add same product to multiple warehouses
- ✅ Real-time total calculation

**Test Cases:**
- [ ] ✅ Add stock to product that exists in warehouse (stock increases)
- [ ] ✅ Add stock to product NOT yet in warehouse (product attached to warehouse automatically)
- [ ] ✅ Add same product to different warehouses (independent stock)
- [ ] ❌ Try entering 0 or negative quantity (validation error)
- [ ] ✅ Add multiple products in single Stock In
- [ ] ✅ Click "+ Tambah Item" adds new row
- [ ] ✅ Click "Delete" removes row
- [ ] ✅ Verify product detail page shows updated stock per warehouse
- [ ] ✅ Edit stock in (can only edit if not referenced by sales)
- [ ] ✅ Delete stock in (stock reverts)

**⚠️ Critical Test: Multi-Warehouse Stock Tracking**
```
TEST: Add ELC-001 (Laptop) to BOTH warehouses

1. Stock In → Main Warehouse → ELC-001 → Qty: 20
   Result: ELC-001 stock in Main WH = 20

2. Stock In → Secondary Warehouse → ELC-001 → Qty: 15
   Result: ELC-001 stock in Secondary WH = 15

3. Go to Product Detail Page (ELC-001)
   Expected:
   ✅ Total Stock: 35 units (20 + 15)
   ✅ Stock Details:
      - Main Warehouse (Jakarta): 20 pcs, Rack: A-01-01
      - Secondary Warehouse (Surabaya): 15 pcs, Rack: [assigned rack]

4. Verify `product_warehouse` table has 2 records:
   - product_id=1, warehouse_id=1, stock=20
   - product_id=1, warehouse_id=2, stock=15
```

---

### **STEP 2.2: Stock Out - Remove Inventory** 📤

**Path:** Dashboard → Transaksi → Stok Keluar → + Tambah Stok Keluar

**Purpose:** Manually remove stock (damaged goods, internal use, etc.)

**Test Data:**

```
Stock Out #1: Damaged Items
----------------------------------------------
Warehouse: Main Warehouse (Jakarta)
Date: [Today's date]
Type: Damaged / Rusak
Notes: Water damage during storage inspection

Items:
  1. ELC-002 (Mouse) - Qty: 3
  2. STN-002 (Paper A4) - Qty: 5

Before:
  - ELC-002 in Main WH: 50 units
  - STN-002 in Main WH: 150 units

After:
  - ELC-002 in Main WH: 50 - 3 = 47 units
  - STN-002 in Main WH: 150 - 5 = 145 units
```

**Expected Result:**
- ✅ Auto-generates Stock Out number (STK-OUT-YYYYMMDD-00001)
- ✅ Stock decreases in warehouse
- ✅ Cannot exceed available stock
- ✅ Shows reason for stock removal

**Test Cases:**
- [ ] ✅ Remove stock from warehouse with sufficient quantity
- [ ] ❌ Try removing more than available stock (validation error)
- [ ] ❌ Try removing stock from warehouse that doesn't have the product
- [ ] ✅ Product dropdown only shows products IN selected warehouse
- [ ] ✅ Stock updates immediately after submission
- [ ] ✅ Delete stock out (stock reverts)

**⚠️ Critical Test: Warehouse-Specific Validation**
```
TEST: Stock Out validates warehouse stock, not total stock

Setup:
- ELC-001 (Laptop) has:
  - Main WH: 20 units
  - Secondary WH: 15 units
  - TOTAL: 35 units

Test:
1. Create Stock Out from Main Warehouse
2. Select ELC-001
3. Try entering Qty: 25 (more than Main WH stock, but less than total)
4. Expected: ❌ Validation error: "Insufficient stock in Main Warehouse"
5. Try entering Qty: 15
6. Expected: ✅ Success
7. Verify: Main WH now has 20 - 15 = 5 units
8. Verify: Secondary WH still has 15 units (unchanged)
```

---

### **STEP 2.3: Stock Opname - Inventory Adjustment** 📊

**Path:** Dashboard → Transaksi → Stok Opname → + Tambah Stok Opname

**Purpose:** Adjust stock to match physical count (fix discrepancies)

**Test Data:**

```
Stock Opname #1: Physical Count Adjustment
----------------------------------------------
Warehouse: Main Warehouse (Jakarta)
Date: [Today's date]
Notes: Monthly inventory count - found discrepancies

Items to adjust:
  1. ELC-001 (Laptop)
     - System Qty: 20 (auto-filled from database)
     - Actual Qty: 18 (2 units missing - theft or loss)
     - Difference: -2
     - Notes: 2 units unaccounted for
     
  2. STN-001 (Pen Box)
     - System Qty: 100
     - Actual Qty: 105 (found 5 extra boxes in storage)
     - Difference: +5
     - Notes: Found in back storage room
```

**Expected Result:**
- ✅ System automatically calculates difference (Actual - System)
- ✅ Stock adjusted to match actual count
- ✅ Can adjust multiple products in one opname
- ✅ Tracks who made adjustment and when

**Test Cases:**
- [ ] ✅ Adjust stock UP (actual > system) - Stock increases
- [ ] ✅ Adjust stock DOWN (actual < system) - Stock decreases
- [ ] ✅ No change (actual = system) - No adjustment made
- [ ] ✅ System Qty auto-fills from warehouse stock
- [ ] ✅ Difference auto-calculates when actual qty changed
- [ ] ❌ Try entering negative actual qty (validation error)
- [ ] ✅ Warehouse dropdown filters products correctly
- [ ] ✅ Product detail page shows adjusted stock
- [ ] ✅ View opname detail shows before/after comparison

**⚠️ Critical Test: Warehouse-Specific Opname**
```
TEST: Opname only affects selected warehouse

Setup:
- ELC-001 in Main WH: 20 units
- ELC-001 in Secondary WH: 15 units

Test:
1. Create Stock Opname for Main Warehouse
2. Select ELC-001
3. System Qty shows: 20 (from Main WH, not total)
4. Enter Actual Qty: 18
5. Save
6. Verify:
   ✅ ELC-001 in Main WH: 18 units (adjusted)
   ✅ ELC-001 in Secondary WH: 15 units (unchanged)
   ✅ Total: 33 units (18 + 15)
```

---

## 🔄 PHASE 3: Inter-Warehouse Transfer

### **STEP 3.1: Create Transfer Request** 🚚

**Path:** Dashboard → Transaksi → Transfer Antar Gudang → + Buat Transfer

**Purpose:** Move products between warehouses

**Test Data:**

```
Transfer #1: Jakarta → Surabaya
----------------------------------------------
From: Main Warehouse (Jakarta)
To: Secondary Warehouse (Surabaya)
Date: [Today's date]
Notes: Restocking Surabaya branch for high demand

Items:
  1. ELC-002 (Mouse) - Qty: 20
  2. STN-001 (Pen Box) - Qty: 30

Before Transfer:
  Main WH:
    - ELC-002: 47 units
    - STN-001: 105 units
  Secondary WH:
    - ELC-002: 0 units (not yet in this warehouse)
    - STN-001: 0 units

After Transfer (when completed):
  Main WH:
    - ELC-002: 47 - 20 = 27 units
    - STN-001: 105 - 30 = 75 units
  Secondary WH:
    - ELC-002: 0 + 20 = 20 units (product added to warehouse)
    - STN-001: 0 + 30 = 30 units
```

**Expected Result:**
- ✅ Auto-generates Transfer number (TRF-YYYYMMDD-00001)
- ✅ Status = "Pending" (not completed yet)
- ✅ Stock NOT deducted yet (only when completed)
- ✅ Product dropdown shows only products in source warehouse
- ✅ Cannot transfer to same warehouse

**Test Cases:**
- [ ] ✅ Create transfer between different warehouses
- [ ] ❌ Try selecting same warehouse as source and destination (validation error)
- [ ] ❌ Try transferring more than available in source warehouse
- [ ] ❌ Try transferring product that doesn't exist in source warehouse
- [ ] ✅ Transfer multiple products in one request
- [ ] ✅ Status shows "Pending" badge (yellow)
- [ ] ✅ Edit transfer while still pending
- [ ] ✅ Delete transfer while still pending

---

### **STEP 3.2: Complete Transfer** ✅

**Path:** Transfer Detail → "Tandai Selesai" Button

**Purpose:** Confirm goods received, execute stock movement

**Test Action:**
1. Open the transfer you created in Step 3.1
2. Click "Tandai Selesai" button
3. Confirm the action

**Expected Result:**
- ✅ Status changes from "Pending" to "Completed" (green badge)
- ✅ Stock DEDUCTED from source warehouse
- ✅ Stock ADDED to destination warehouse
- ✅ If product doesn't exist in destination, it's automatically added
- ✅ Cannot edit or delete after completion
- ✅ "Tandai Selesai" button disappears

**Test Cases:**
- [ ] ✅ Complete transfer with products that exist in destination
- [ ] ✅ Complete transfer with products that DON'T exist in destination (auto-attach)
- [ ] ✅ Verify source warehouse stock decreased
- [ ] ✅ Verify destination warehouse stock increased
- [ ] ✅ Check product detail page shows correct stock in both warehouses
- [ ] ❌ Try editing completed transfer (button disabled)
- [ ] ❌ Try deleting completed transfer (should show error)

**⚠️ Critical Test: Product Auto-Assignment to Destination**
```
TEST: Transfer product that doesn't exist in destination warehouse

Setup:
- ELC-002 (Mouse) exists ONLY in Main WH: 47 units
- Secondary WH does NOT have ELC-002 yet

Test:
1. Create Transfer: Main WH → Secondary WH
2. Add ELC-002, Qty: 20
3. Complete Transfer
4. Verify:
   ✅ Main WH: 47 - 20 = 27 units
   ✅ Secondary WH: 0 + 20 = 20 units (product automatically added)
   ✅ ELC-002 now appears in Secondary WH product list
   ✅ product_warehouse table has new record:
      - product_id=2, warehouse_id=2, stock=20, rack_location=[from transfer or default]
```

---

## 🛒 PHASE 4: Sales Cycle

### **STEP 4.1: Create Customer** 🆕

**Path:** Dashboard → Penjualan → Pelanggan → + Tambah Pelanggan

**Purpose:** Register customers for sales orders and invoicing

**Test Data (Create 2 customers):**
```
Customer #1: PT. Maju Jaya Indonesia
   - Address: Jl. Sudirman No. 123, Jakarta Pusat 10110
   - Phone: 021-12345678
   - Email: purchasing@majujaya.co.id
   - NPWP: 01.234.567.8-901.000 (optional)
   - Notes: VIP Customer - Payment terms NET 30
   - Active: ✓ Checked

Customer #2: CV. Berkah Sentosa
   - Address: Jl. Ahmad Yani No. 456, Surabaya 60234
   - Phone: 031-98765432
   - Email: admin@berkahsentosa.com
   - NPWP: 02.345.678.9-012.000
   - Notes: Regular customer - COD payment
   - Active: ✓ Checked
```

**Expected Result:**
- ✅ Success message displayed
- ✅ Redirected to customer detail page
- ✅ Customer appears in customers list
- ✅ Customer available in Sales Order dropdown

**Test Cases:**
- [ ] ✅ Create customer with all fields
- [ ] ✅ Create customer with required fields only (Name, Address, Phone)
- [ ] ❌ Try creating duplicate customer name (error: "The name has already been taken")
- [ ] ❌ Try creating without name (validation error)
- [ ] ❌ Try creating without phone (validation error)
- [ ] ❌ Try creating without address (validation error)
- [ ] ✅ Create customer without email (email is optional)
- [ ] ✅ Create customer without NPWP (NPWP is optional)
- [ ] ✅ Search for customer in list
- [ ] ✅ Filter by active/inactive status
- [ ] ✅ Edit customer information
- [ ] ✅ Deactivate customer (won't appear in SO dropdown)
- [ ] ❌ Try deleting customer with sales orders (should show error)

**⚠️ Important Validation Rules:**
- **Name** = REQUIRED + UNIQUE (no duplicates allowed)
- **Address** = REQUIRED (needed for delivery/Surat Jalan)
- **Phone** = REQUIRED (needed for order confirmation)
- **Email** = OPTIONAL (not all companies have email)
- **NPWP** = OPTIONAL (only for tax-registered companies)

---

### **STEP 4.2: Create Sales Order** 📦

**Path:** Dashboard → Penjualan → Pesanan Penjualan → + Buat Pesanan

**Purpose:** Create sales order that will deduct stock from specific warehouse

**Test Data:**
```
Sales Order #1: Jakarta Customer
----------------------------------------------
Customer: PT. Maju Jaya Indonesia
Warehouse: Main Warehouse (Jakarta)
Order Date: [Today's date]
Delivery Date: [3 days from today]
Notes: Urgent order - Ship by end of week

Products (from Main Warehouse):
  1. ELC-001 (Laptop) - Qty: 5 - Price: 9,500,000 (auto-filled)
  2. ELC-002 (Mouse) - Qty: 10 - Price: 200,000
  3. STN-002 (Paper A4) - Qty: 20 - Price: 45,000

Discount: 500,000
Tax (PPN 11%): Auto-calculated
Total: Auto-calculated

Expected Calculation:
  Subtotal: (5×9,500,000) + (10×200,000) + (20×45,000) = 49,900,000
  After Discount: 49,900,000 - 500,000 = 49,400,000
  PPN 11%: 49,400,000 × 0.11 = 5,434,000
  TOTAL: 49,400,000 + 5,434,000 = Rp 54,834,000
```

**Expected Result:**
- ✅ Auto-generates SO number (SO-YYYYMMDD-00001)
- ✅ Product dropdown shows only products in selected warehouse
- ✅ Product prices auto-fill from database (selling_price)
- ✅ Real-time calculation works correctly
- ✅ Status is "Draft"
- ✅ Payment status is "Unpaid"
- ✅ Can edit while in Draft status

**Test Cases:**
- [ ] ✅ Create SO for Main Warehouse customer
- [ ] ✅ Product dropdown filtered by selected warehouse
- [ ] ✅ Prices auto-fill correctly
- [ ] ✅ Quantity change recalculates subtotal
- [ ] ✅ Discount updates total
- [ ] ✅ PPN 11% calculates correctly
- [ ] ✅ Click "+ Tambah Produk" adds new row
- [ ] ✅ Delete button removes row
- [ ] ✅ All prices formatted as Rupiah
- [ ] ❌ Try selecting product that doesn't exist in warehouse (shouldn't appear in dropdown)

**⚠️ Critical Test: Warehouse-Specific Product List**
```
TEST: Product dropdown only shows products in selected warehouse

Setup:
- Main WH has: ELC-001, ELC-002, STN-001, STN-002
- Secondary WH has: FRN-001, ELC-002 (from transfer)

Test:
1. Create Sales Order → Select Main Warehouse
2. Click product dropdown
3. Expected: Shows ELC-001, ELC-002, STN-001, STN-002 ONLY
4. Should NOT show: FRN-001 (exists only in Secondary WH)

5. Change Warehouse to Secondary Warehouse
6. Click product dropdown again
7. Expected: Shows FRN-001, ELC-002 ONLY
8. Should NOT show: ELC-001, STN-001, STN-002
```

---

### **STEP 4.3: Edit Sales Order** ✏️

**Path:** Pesanan Penjualan → [Your SO] → Edit

**Test Cases:**
- [ ] ✅ Can only edit if status is "Draft"
- [ ] ❌ Try editing confirmed order (button disabled/error)
- [ ] ✅ Change quantity of Laptop from 5 to 3
- [ ] ✅ Add 4th product (STN-001 Pen Box, Qty: 10)
- [ ] ✅ Remove Paper A4 from order
- [ ] ✅ Change discount from 500,000 to 1,000,000
- [ ] ✅ Save changes
- [ ] ✅ Verify totals recalculated correctly
- [ ] ✅ Verify product list updated

---

### **STEP 4.4: Confirm Order** ✔️

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Konfirmasi Pesanan"

**Purpose:** Lock the order and validate warehouse has enough stock

**Expected Result:**
- ✅ System validates stock availability IN THE SELECTED WAREHOUSE
- ✅ If insufficient → Error message shows which products lack stock
- ✅ If sufficient → Status changes from "Draft" to "Confirmed"
- ✅ "Edit" and "Delete" buttons disappear (order locked)
- ✅ New buttons appear: "Tandai Dikirim", "Generate Stok Keluar", "Batalkan Pesanan"
- ✅ Stock NOT deducted yet (only validated)

**Test Cases:**
- [ ] ✅ Confirm with sufficient stock in warehouse
- [ ] ❌ Try confirming with insufficient stock (validation error)
- [ ] ✅ Error message clearly shows which products have insufficient stock
- [ ] ✅ Verify stock NOT changed after confirmation (check product detail)
- [ ] ✅ Status badge changes from gray (Draft) to blue (Confirmed)
- [ ] ❌ Try editing after confirmation (button disabled)
- [ ] ❌ Try deleting after confirmation (should show error)

**⚠️ Critical Test: Warehouse-Specific Stock Validation**
```
TEST: Validation uses warehouse stock, not total stock

Setup:
- ELC-001 (Laptop) stock:
  - Main WH: 18 units (after opname adjustment)
  - Secondary WH: 15 units
  - TOTAL: 33 units

Test:
1. Create SO for Main Warehouse
2. Add ELC-001, Qty: 25 (more than Main WH stock, but less than total)
3. Click "Konfirmasi Pesanan"
4. Expected: ❌ Error: "Insufficient stock for ELC-001 in Main Warehouse"
5. Error should show: Available: 18, Required: 25

6. Edit SO → Change Qty to 10 (less than Main WH stock)
7. Click "Konfirmasi Pesanan" again
8. Expected: ✅ Success - Order confirmed
```

---

### **STEP 4.5: Generate Stock Out** 📤

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Generate Stok Keluar"

**Purpose:** Physically deduct stock from warehouse inventory

**Expected Result:**
- ✅ Auto-creates new Stock Out record (STK-OUT-YYYYMMDD-XXXXX)
- ✅ Creates Stock Out Detail records for each SO item
- ✅ **Deducts stock from warehouse** using atomic DB operations
- ✅ Links Stock Out to Sales Order (stock_out_id field populated)
- ✅ Success message with link to Stock Out detail
- ✅ "Generate Stok Keluar" button disappears (already generated)
- ✅ "Lihat Stok Keluar" link appears

**Stock Deduction Example:**
```
Before Generate Stock Out (Main Warehouse):
  - ELC-001 (Laptop): 18 units
  - ELC-002 (Mouse): 27 units
  - STN-001 (Pen Box): 75 units

Sales Order Items:
  - ELC-001: Qty 3
  - ELC-002: Qty 10
  - STN-001: Qty 10

After Generate Stock Out:
  - ELC-001: 18 - 3 = 15 units
  - ELC-002: 27 - 10 = 17 units
  - STN-001: 75 - 10 = 65 units
```

**Test Cases:**
- [ ] ✅ Click "Generate Stok Keluar" button
- [ ] ✅ Stock Out record created with correct type ("Sales")
- [ ] ✅ All SO items transferred to Stock Out details
- [ ] ✅ Stock deducted from correct warehouse
- [ ] ✅ Button changes to "Lihat Stok Keluar" with link
- [ ] ✅ Go to Stok Keluar list → New record visible
- [ ] ✅ Open Stock Out detail → Verify linked to SO
- [ ] ✅ Go to Products → Verify stock reduced correctly
- [ ] ✅ Check product detail → Verify warehouse-specific stock updated
- [ ] ❌ Try generating Stock Out again (button disabled)
- [ ] ❌ Try deleting Stock Out linked to SO (should show error)

**⚠️ Critical Test: Stock Deducted from Correct Warehouse**
```
TEST: Stock Out affects only the SO's warehouse

Setup:
- Before Stock Out generation:
  - ELC-002 in Main WH: 27 units
  - ELC-002 in Secondary WH: 20 units
  - TOTAL: 47 units

Test:
1. Sales Order from Main Warehouse
2. ELC-002, Qty: 10
3. Generate Stock Out
4. Verify:
   ✅ ELC-002 in Main WH: 27 - 10 = 17 units
   ✅ ELC-002 in Secondary WH: 20 units (unchanged)
   ✅ TOTAL: 37 units (17 + 20)
5. Check Stock Out detail → Warehouse field = Main Warehouse
```

---

### **STEP 4.6: Ship Order** 🚚

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Tandai Dikirim"

**Expected Result:**
- ✅ Status changes from "Confirmed" to "Shipped"
- ✅ Status badge turns yellow
- ✅ "Tandai Dikirim" button disappears
- ✅ "Tandai Terkirim" button appears
- ✅ "Cetak Surat Jalan" button enabled

**Test Cases:**
- [ ] Status changes correctly
- [ ] Can still cancel order at this stage
- [ ] Can view delivery order PDF

---

### **STEP 7: View Delivery Order PDF** 📄

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Cetak Surat Jalan"

**Expected Result:**
- ✅ PDF opens in new tab
- ✅ Company info displayed (placeholder)
- ✅ Customer info correct
- ✅ All products listed with quantities
- ✅ Total items count correct
- ✅ Signature sections present (3 columns)
- ✅ Professional layout

**Verify PDF Contains:**
- [ ] SO number as delivery order number
- [ ] Order date and delivery date
- [ ] Warehouse name
- [ ] Customer name, address, phone, NPWP
- [ ] Product table with SKU, quantities
- [ ] Warning message about inspection
- [ ] Notes (if any)

---

### **STEP 8: Deliver Order** ✅

**Path:** Pesanan Penjualan → [Your SO] → Detail → "Tandai Terkirim"

**Expected Result:**
- ✅ Status changes from "Shipped" to "Delivered"
- ✅ Status badge turns green
- ✅ All workflow buttons disappear
- ✅ "Buat Faktur" button appears (if no invoice yet)
- ✅ Cannot edit or cancel anymore

---

### **STEP 9: Create Invoice** 💰

**Path Option 1:** Pesanan Penjualan → [Your SO] → "Buat Faktur"  
**Path Option 2:** Faktur & Pembayaran → + Buat Faktur → Select your SO

**Test Data:**
```
Sales Order: [Your SO] (auto-selected or choose from dropdown)
Invoice Date: [Today's date]
Due Date: [30 days from today - auto-calculated]
Notes: Payment NET 30 days - Transfer to BCA
```

**Expected Result:**
- ✅ Auto-generates Invoice number (INV-YYYYMMDD-00001)
- ✅ Total amount = Sales Order total
- ✅ Paid amount = 0
- ✅ Payment status = "Unpaid"
- ✅ Due date auto-fills (+30 days)
- ✅ Customer info pre-loaded from SO
- ✅ All products from SO displayed

**Validation Tests:**
- [ ] Try creating invoice for non-delivered order ❌
- [ ] Try creating duplicate invoice for same SO ❌
- [ ] Invoice date changes → Due date auto-updates
- [ ] Can only select delivered orders without invoices

---

### **STEP 10: View Invoice PDF** 📑

**Path:** Faktur & Pembayaran → [Your Invoice] → "Lihat Faktur PDF"

**Expected Result:**
- ✅ Professional invoice layout
- ✅ "FAKTUR PAJAK" title
- ✅ Invoice number and dates correct
- ✅ Customer info with NPWP
- ✅ Payment status badge (RED - Belum Dibayar)
- ✅ Product table with prices
- ✅ Totals breakdown:
  - Subtotal
  - Discount (if any)
  - PPN 11%
  - **TOTAL** (bold)
- ✅ Tax info box (yellow background)
- ✅ Bank payment details
- ✅ Signature sections (3 columns)

**Verify PDF Contains:**
- [ ] All product details correct
- [ ] PPN 11% calculated correctly
- [ ] Total matches Sales Order total
- [ ] Payment status badge visible
- [ ] Company NPWP shown
- [ ] Customer NPWP shown (if exists)

---

### **STEP 11: Record Partial Payment** 💵

**Path:** Faktur & Pembayaran → [Your Invoice] → Detail → "Catat Pembayaran" Form

**Test Data (1st Payment):**
```
Amount: 50% of total (e.g., if total is Rp 10,000,000 → enter 5,000,000)
Payment Date: [Today]
Payment Method: Transfer Bank
Notes: Transfer BCA - Ref: TRF20251109001
```

**Expected Result:**
- ✅ Paid amount increases by entered amount
- ✅ Payment status changes to "Partial" (yellow badge)
- ✅ Remaining amount recalculated
- ✅ Payment info appears in sidebar
- ✅ Payment history added (with green checkmark)
- ✅ Payment form still visible (not fully paid)
- ✅ Sales Order payment status also updates to "Partial"

**Validation Tests:**
- [ ] Try paying more than remaining ❌
- [ ] Try paying 0 or negative ❌
- [ ] Payment notes appended correctly
- [ ] Last payment date/method updated

---

### **STEP 12: Record Final Payment** 💰

**Path:** Same as Step 11

**Test Data (2nd Payment):**
```
Amount: Remaining balance (e.g., 5,000,000)
Payment Date: [3 days later]
Payment Method: Tunai
Notes: Cash payment received
```

**Expected Result:**
- ✅ Paid amount = Total amount (fully paid)
- ✅ Payment status changes to "Paid" (green badge)
- ✅ Remaining amount = Rp 0
- ✅ Payment form **disappears** (no longer needed)
- ✅ Both payment records in history
- ✅ Sales Order payment status also updates to "Paid"
- ✅ "Hapus Faktur" button disappears (cannot delete paid invoice)

---

### **STEP 13: Dashboard Verification** 📊

**Path:** Dashboard (Home)

**Verify KPIs Updated:**
- [ ] **Sales This Month** includes your order total
- [ ] **Pending Orders** count decreased (order delivered)
- [ ] **Unpaid Invoices** decreased to 0 (after full payment)
- [ ] **Active Customers** includes new customer
- [ ] **Recent Sales Orders** shows your SO in list
- [ ] **Recent Invoices** shows your invoice in list

---

## 🔄 Additional Test Scenarios

### **Cancel Order Workflow**

**Test Case 1: Cancel Draft Order**
- [ ] Create new SO → Leave as Draft → Cancel
- [ ] Verify order marked as "Cancelled"
- [ ] Cannot edit or change status after cancellation

**Test Case 2: Cancel Confirmed Order**
- [ ] Create SO → Confirm → Cancel (before shipping)
- [ ] Verify order marked as "Cancelled"
- [ ] Stock NOT deducted (if Stock Out not generated)

**Test Case 3: Cannot Cancel After Shipped**
- [ ] Create SO → Confirm → Ship → Try to Cancel ❌
- [ ] Should show error or button disabled

---

### **Delete Restrictions**

**Test Case 1: Delete Draft SO**
- [ ] Create SO → Keep as Draft → Delete ✅
- [ ] Should work without issues

**Test Case 2: Cannot Delete Confirmed SO**
- [ ] Create SO → Confirm → Try Delete ❌
- [ ] Should show error message

**Test Case 3: Delete Unpaid Invoice**
- [ ] Create Invoice → Keep Unpaid → Delete ✅
- [ ] SO payment status resets to "Unpaid"

**Test Case 4: Cannot Delete Paid Invoice**
- [ ] Create Invoice → Record Payment → Try Delete ❌
- [ ] Should show error message

---

### **Edit Restrictions**

**Test Case 1: Edit Only Draft SO**
- [ ] Try editing Confirmed SO → Error ❌
- [ ] Try editing Shipped SO → Error ❌
- [ ] Try editing Delivered SO → Error ❌

**Test Case 2: Edit Only Unpaid Invoice**
- [ ] Try editing Partial invoice → Error ❌
- [ ] Try editing Paid invoice → Error ❌

---

### **Stock Validation**

**Test Case 1: Insufficient Stock**
- [ ] Create SO with quantity > available stock
- [ ] Try to confirm → Should show error ❌
- [ ] Error message shows which products lack stock

**Test Case 2: Stock Deduction**
- [ ] Note product stock before order
- [ ] Create SO → Confirm → Generate Stock Out
- [ ] Verify stock reduced by exact quantity
- [ ] Check product detail page shows correct stock

---

### **Customer Integration**

**Test Case 1: Customer Detail Page**
- [ ] Go to customer detail page
- [ ] Verify "Sales Orders" section lists all customer orders
- [ ] Click SO link → Should open order detail

**Test Case 2: Cannot Delete Customer with Orders**
- [ ] Create SO for customer
- [ ] Try to delete customer ❌
- [ ] Should show error about existing orders

---

### **Filter & Search Tests**

**Sales Orders:**
- [ ] Search by SO number
- [ ] Search by customer name
- [ ] Filter by status (Draft/Confirmed/Shipped/Delivered/Cancelled)
- [ ] Filter by payment status
- [ ] Filter by customer dropdown
- [ ] Filter by date range
- [ ] Combine multiple filters

**Invoices:**
- [ ] Search by invoice number
- [ ] Search by customer name
- [ ] Filter by payment status
- [ ] Filter by customer dropdown
- [ ] Filter by invoice date range
- [ ] Filter by due date range
- [ ] Identify overdue invoices (red text)

**Customers:**
- [ ] Search by name/phone/email/NPWP
- [ ] Filter by active/inactive status

---

## 🐛 Known Issues / Edge Cases to Test

### 1. **Concurrent Stock Updates**
- [ ] Two users confirm orders for same product at same time
- [ ] Stock should handle correctly

### 2. **Date Validation**
- [ ] Invoice date before order date → Should work (flexible)
- [ ] Due date before invoice date → Should show error

### 3. **Number Formatting**
- [ ] Large amounts (> 1 billion) → Should format correctly
- [ ] Decimal amounts → Should round to 2 decimals

### 4. **PDF Generation**
- [ ] Test on different browsers (Chrome, Firefox, Edge)
- [ ] Test PDF download vs. view in browser
- [ ] Verify PDFs work with special characters in customer names

### 5. **Payment Recording**
- [ ] Multiple partial payments (3+ times)
- [ ] Payment notes with special characters
- [ ] Very small payment amount (Rp 1)

---

## ✅ Success Criteria

The feature is **READY FOR PRODUCTION** if:

- ✅ All 13 main workflow steps complete without errors
- ✅ Stock deduction works correctly
- ✅ Payment tracking accurate (no rounding errors)
- ✅ PDFs generate properly
- ✅ Dashboard KPIs update in real-time
- ✅ Status workflow enforced (cannot skip steps)
- ✅ Delete/edit restrictions work as designed
- ✅ No console errors in browser
- ✅ No server errors in logs

---

## 📝 Test Results Template

```
TESTER: ___________________
DATE: November 9, 2025
ENVIRONMENT: Local / Staging / Production

STEP 1 - Create Customer: ✅ PASS / ❌ FAIL
STEP 2 - Create SO: ✅ PASS / ❌ FAIL
STEP 3 - Edit SO: ✅ PASS / ❌ FAIL
STEP 4 - Confirm Order: ✅ PASS / ❌ FAIL
STEP 5 - Generate Stock Out: ✅ PASS / ❌ FAIL
STEP 6 - Ship Order: ✅ PASS / ❌ FAIL
STEP 7 - Delivery Order PDF: ✅ PASS / ❌ FAIL
STEP 8 - Deliver Order: ✅ PASS / ❌ FAIL
STEP 9 - Create Invoice: ✅ PASS / ❌ FAIL
STEP 10 - Invoice PDF: ✅ PASS / ❌ FAIL
STEP 11 - Partial Payment: ✅ PASS / ❌ FAIL
STEP 12 - Final Payment: ✅ PASS / ❌ FAIL
STEP 13 - Dashboard KPIs: ✅ PASS / ❌ FAIL

BUGS FOUND: _______________________
CRITICAL ISSUES: __________________
NOTES: ____________________________
```

---

## 🚀 Next Steps After Testing

1. **If all tests pass:**
   - ✅ Mark Phase 2 as complete
   - ✅ Deploy to staging/production
   - ✅ Train users on new features
   - ✅ Update user documentation

2. **If bugs found:**
   - 🐛 Document all issues
   - 🐛 Prioritize by severity
   - 🐛 Fix critical bugs first
   - 🐛 Retest after fixes

---

**Good luck with testing! 🎉**
