# Prescription Stock Management System

## Overview
This document describes the prescription stock management system implemented for the healthcare application. The system consists of three main models that work together to track medicine inventory, income (stock additions), and outcomes (stock reductions).

## Models Created

### 1. PrescriptionStock Model
**File:** `app/Models/PrescriptionStock.php`

**Purpose:** Tracks current stock levels for medicines linked to prescription items.

**Key Fields:**
- `prescription_item_id` - Links to PrescriptionItem (nullable)
- `medicine_id` - Identifies the medicine
- `current_stock` - Total available quantity
- `reserved_stock` - Quantity reserved for prescriptions
- `available_stock` - Calculated available quantity
- `minimum_stock` - Low stock threshold
- `maximum_stock` - Maximum stock threshold
- `last_updated` - Timestamp of last update
- `notes` - Additional notes

**Helper Methods:**
- `calculateAvailableStock()` - Calculates available stock
- `isLowStock()` - Checks if stock is below minimum
- `isOverstocked()` - Checks if stock exceeds maximum

### 2. Income Model
**File:** `app/Models/Income.php`

**Purpose:** Tracks medicine stock additions (purchases, donations, returns, etc.).

**Key Fields:**
- `medicine_id` - Identifies the medicine
- `amount` - Quantity received
- `batch_number` - Batch identification
- `expiry_date` - Expiration date
- `supplier_name` - Supplier information
- `purchase_price` - Cost per unit
- `purchase_date` - Date of purchase
- `invoice_number` - Supplier invoice
- `income_type` - Type of income (purchase, return, donation, transfer, adjustment)

**Helper Methods:**
- `isExpired()` - Checks if medicine is expired
- `isExpiringSoon()` - Checks if expiring within 30 days
- `getTotalValue()` - Calculates total value

### 3. Outcome Model
**File:** `app/Models/Outcome.php`

**Purpose:** Tracks medicine stock reductions (prescriptions, expirations, damages, etc.).

**Key Fields:**
- `medicine_id` - Identifies the medicine
- `amount` - Quantity dispensed/lost
- `prescription_item_id` - Links to prescription if applicable
- `patient_id` - Patient if prescription-related
- `doctor_id` - Prescribing doctor
- `outcome_type` - Type of outcome (prescription, expired, damaged, lost, return, adjustment)
- `batch_number` - Batch identification
- `outcome_date` - Date of outcome
- `reason` - Reason for outcome

**Helper Methods:**
- `isPrescriptionOutcome()` - Checks if prescription-related
- `isExpirationOutcome()` - Checks if due to expiration
- `isDamageOutcome()` - Checks if due to damage
- `getOutcomeDescription()` - Gets human-readable description

## Database Migration

**File:** `database/migrations/2025_08_20_084735_create_prescription_stock_management_tables.php`

### Tables Created:
1. **prescription_stocks** - Stock tracking table
2. **incomes** - Stock addition tracking table
3. **outcomes** - Stock reduction tracking table

### Features:
- **Foreign Key Constraints** - Ensures data integrity
- **Indexes** - Optimized for performance
- **Soft Deletes** - Maintains data history
- **Audit Trail** - Tracks who created/updated/deleted records
- **Enum Fields** - Restricted values for type fields

## Relationships

### PrescriptionStock Relationships:
- `belongsTo(PrescriptionItem::class)` - Links to prescription item
- `belongsTo(Medicine::class)` - Links to medicine
- `hasMany(Income::class)` - Related income records
- `hasMany(Outcome::class)` - Related outcome records

### Income Relationships:
- `belongsTo(Medicine::class)` - Links to medicine
- `belongsTo(PrescriptionStock::class)` - Links to stock record

### Outcome Relationships:
- `belongsTo(Medicine::class)` - Links to medicine
- `belongsTo(PrescriptionItem::class)` - Links to prescription item
- `belongsTo(Patient::class)` - Links to patient
- `belongsTo(User::class)` - Links to doctor

### Updated Existing Models:
- **PrescriptionItem** - Added relationships to PrescriptionStock and Outcome
- **Medicine** - Added relationships to PrescriptionStock, Income, and Outcome

## Seeder

**File:** `database/seeders/PrescriptionStockSeeder.php`

**Purpose:** Populates sample data for testing and development.

**Features:**
- Creates sample stock records for existing medicines
- Generates realistic income records with various types
- Creates outcome records with different scenarios
- Links to existing users, patients, and prescription items

## Usage Examples

### Creating a Stock Record:
```php
$stock = PrescriptionStock::create([
    'medicine_id' => $medicine->id,
    'current_stock' => 100,
    'minimum_stock' => 10,
    'maximum_stock' => 500,
    'notes' => 'Initial stock'
]);
```

### Recording Income:
```php
$income = Income::create([
    'medicine_id' => $medicine->id,
    'amount' => 50,
    'batch_number' => 'BATCH-12345',
    'expiry_date' => '2025-12-31',
    'supplier_name' => 'PharmaCorp',
    'purchase_price' => 25.50,
    'income_type' => 'purchase'
]);
```

### Recording Outcome:
```php
$outcome = Outcome::create([
    'medicine_id' => $medicine->id,
    'amount' => 5,
    'prescription_item_id' => $prescriptionItem->id,
    'patient_id' => $patient->id,
    'doctor_id' => $doctor->id,
    'outcome_type' => 'prescription',
    'reason' => 'Prescribed to patient'
]);
```

### Checking Stock Status:
```php
$stock = PrescriptionStock::where('medicine_id', $medicineId)->first();
if ($stock->isLowStock()) {
    // Send low stock alert
}
```

## Benefits

1. **Complete Inventory Tracking** - Full visibility of medicine stock levels
2. **Audit Trail** - Complete history of all stock movements
3. **Batch Management** - Track medicines by batch numbers
4. **Expiry Management** - Monitor expiration dates
5. **Supplier Management** - Track suppliers and purchase information
6. **Performance Optimized** - Proper indexing for fast queries
7. **Data Integrity** - Foreign key constraints prevent orphaned records
8. **Flexible** - Supports various types of income and outcomes

## Next Steps

1. **Controllers** - Create controllers for managing the stock system
2. **Views** - Build user interfaces for stock management
3. **Reports** - Generate stock reports and analytics
4. **Notifications** - Implement low stock alerts
5. **API Endpoints** - Create RESTful APIs for mobile apps
6. **Validation** - Add form validation rules
7. **Testing** - Write unit and feature tests
