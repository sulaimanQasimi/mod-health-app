# Medical Database Application - Project Report
## Project Overview
This is a comprehensive medical database application built with Laravel 10, designed for healthcare management. The application includes patient management, medical procedures, pharmacy management, and various healthcare modules.

## Technology Stack
- **Framework**: Laravel 10.10
- **PHP Version**: 8.1+
- **Database**: SQLite (development)
- **Frontend**: Bootstrap 5, Vue.js
- **Additional Packages**: 
  - Laravel Sanctum (API authentication)
  - Spatie Laravel Permission (role-based access control)
  - Laravel Excel (data export/import)
  - MPDF (PDF generation)
  - Verta (Persian date handling)
  - Laravel Backup (data backup)
  - **Sulaiman Qasimi / mohammadrafi10 Router** (custom routing package)

## Developers Contributions

### Sulaiman Qasimi and Mohammad Rafi 10 Contributions
**GitHub Repository (Sulaiman Qasimi)**: https://github.com/sulaimanQasimi/
**GitHub Repository (Mohammad Rafi 10)**: https://github.com/mohhamdrafi10/
**Main Project Repository**: https://github.com/sulaimanQasimi/mod-health-app
**Collaborative Repository**: https://github.com/mohhamdrafi10/mod-health-app

#### Custom Router Package Integration
- **Package**: `sulaimanqasimi/router` (dev-main)
- **Purpose**: Custom routing functionality for the medical application
- **Integration**: Added custom router package to composer.json with VCS repository configuration
- **Features**: Enhanced routing capabilities beyond standard Laravel routing

#### Technical Contributions:
- **Custom Router Implementation**: Developed specialized routing system for medical database application
- **Package Management**: Integrated custom router package with proper VCS configuration
- **Repository Configuration**: Set up GitHub repository integration for custom package development

### Mohammad Rafi 10 and Sulaiman Qasimi Contributions
**Email (Mohammad Rafi 10)**: mohammadrafishirzai83@gmail.com
**Email (Sulaiman Qasimi)**: sulaimanqasimy@gmail.com
**Primary Repository**: https://github.com/sulaimanQasimi/mod-health-app
**Secondary Repository**: https://github.com/mohhamdrafi10/mod-health-app

#### Database Seeding & Data Management
- **User Seeding**: Implemented comprehensive user seeding with test accounts
- **District Data Management**: Added extensive district data for Afghanistan provinces
- **Geographic Data**: Contributed to location-based data structure for patient management

#### Specific Contributions:
- **UserSeeder.php**: Created test user accounts including admin and medical staff accounts
- **DistrictSeeder.php**: Implemented comprehensive district data covering all Afghan provinces
- **Data Localization**: Added support for multiple languages (English, Dari, Pashto) in geographic data

## Repository Structure & Collaboration

### GitHub Repository Management
- **Primary Repository**: https://github.com/sulaimanQasimi/mod-health-app
  - Main development repository
  - Primary source for releases and documentation
  - Integration with custom router package

- **Collaborative Repository**: https://github.com/mohhamdrafi10/mod-health-app
  - Secondary development repository
  - Database seeding and geographic data contributions
  - Cross-repository synchronization

### Repository Synchronization
- **Cross-Repository Development**: Both repositories maintain synchronized development
- **Feature Branch Strategy**: Development features are shared between repositories
- **Code Review Process**: Collaborative review process across both repositories
- **Release Management**: Coordinated releases from primary repository

## Recent Major Changes & New Features

### 1. Users Management Interface Enhancements (NEW)
**Implementation Date**: January 2026
**Contributors**: Development Team

#### Statistics Cards Accuracy Fix:
- **Problem Identified**: Statistics cards were displaying incorrect counts when total users exceeded the pagination limit
- **Root Cause**: Statistics were calculated using paginated collection instead of full collection
- **Impact**: When 21 users existed, statistics showed only 20 (the paginated page size)
- **Solution**: Separated statistics calculation from pagination to use full collection

#### Per-Page Selector Implementation:
- **New Feature**: Added per-page selector dropdown to pagination footer
- **User Control**: Users can now select how many items to display per page (10, 20, 50, 100)
- **Default Value**: Defaults to 20 items per page
- **Dynamic Pagination**: Pagination links automatically preserve the selected per-page value

#### Technical Implementation:
- **Controller Changes** (`app/Http/Controllers/UserController.php`):
  - Added full collection retrieval before pagination: `$allUsers = $query->get()`
  - Implemented per-page parameter handling: `$perPage = $request->get('per_page', 20)`
  - Updated pagination to use dynamic per-page value: `$query->paginate($perPage)`
  - Passed `$allUsers` collection to view for accurate statistics
- **View Updates** (`resources/views/pages/users/index.blade.php`):
  - Updated all 4 statistics cards to use `$allUsers` instead of `$users`
    - Active users count: `$allUsers->where('status', 1)->count()`
    - Deactive users count: `$allUsers->where('status', 0)->count()`
    - Total users count: `$allUsers->count()`
    - New users count: `$allUsers->filter(...)->count()`
  - Added per-page selector dropdown in pagination footer
  - Implemented JavaScript handler for per-page selection with URL parameter preservation
  - Enhanced pagination footer layout with flex-wrap for responsive design

#### Files Modified:
- **app/Http/Controllers/UserController.php**: 
  - Added full collection retrieval for statistics
  - Implemented per-page parameter handling
  - Updated pagination logic
- **resources/views/pages/users/index.blade.php**: 
  - Fixed statistics cards to use full collection
  - Added per-page selector dropdown
  - Enhanced pagination footer with JavaScript functionality

#### Impact:
- **Accurate Statistics**: Statistics cards now display correct counts for all users regardless of pagination
- **Improved User Experience**: Users can control how many items are displayed per page
- **Better Performance**: Users can optimize page load by selecting appropriate per-page value
- **Enhanced Usability**: More flexible data viewing options for different user needs
- **Data Integrity**: Statistics reflect actual database counts, not just current page data

### 2. Income Management System Enhancements (NEW)
**Implementation Date**: January 2026
**Contributors**: Development Team

#### Income Type Expansion:
- **New Income Type Added**: Added 'completion' (اکمال) as a new income type option
- **Database Schema Update**: Created migration to extend `income_type` ENUM column to include 'completion'
- **Controller Updates**: Updated `IncomeController` to support the new income type in validation and type arrays
- **Form Enhancement**: Added 'completion' option to income creation form with proper localization

#### Purchase Price Field Enhancement:
- **Optional Purchase Price**: Made `purchase_price` field optional in income creation form
- **Validation Update**: Changed validation rule from `required` to `nullable` for purchase price
- **User Experience**: Improved form flexibility for different income types that may not require purchase price

#### Technical Implementation:
- **Migration**: `2026_01_03_100000_add_completion_to_income_type_enum.php`
  - Extended ENUM column: `['purchase', 'return', 'donation', 'transfer', 'adjustment', 'completion']`
  - Proper rollback support in migration down method
- **Controller Changes**: 
  - Updated `$incomeTypes` array to include 'completion'
  - Updated validation rules to accept 'completion' as valid income type
  - Changed `purchase_price` validation from `required` to `nullable`
- **View Updates**: 
  - Added 'completion' option to income type select dropdown
  - Removed `required` attribute from purchase_price input field
  - Proper localization support for new income type

#### Files Modified:
- **database/migrations/2026_01_03_100000_add_completion_to_income_type_enum.php**: New migration for enum extension
- **app/Http/Controllers/IncomeController.php**: Updated income types array and validation rules
- **resources/views/pages/incomes/create.blade.php**: Added completion option and made purchase_price optional
- **lang/dr/global.php**: Translation key for 'completion' already exists (اکمال)

#### Impact:
- **Enhanced Income Tracking**: More comprehensive income type categorization
- **Improved Flexibility**: Optional purchase price allows for different income scenarios
- **Better Localization**: Proper Dari translation support for new income type
- **Database Consistency**: Proper schema migration ensures data integrity

### 2. Laboratory Results Interface Enhancements (NEW)
**Implementation Date**: January 2025
**Contributors**: Mohammad Rafi 10 & Sulaiman Qasimi

#### Dari Date Picker Integration:
- **Laboratory Results Grouped Page** (`resources/views/pages/laboratory/results/grouped.blade.php`):
  - **Date Input Standardization**: Converted HTML5 date inputs to Persian date picker format
  - **Dari Calendar Integration**: Implemented `datepicker_dari` class with `pdp-el` styling
  - **JavaScript Initialization**: Added Persian date picker script with proper configuration
  - **Form Layout Optimization**: Fixed button layout issues with `btn-sm` and `gap-1` classes
  - **Background Color Removal**: Eliminated accordion table background colors for cleaner interface

#### Technical Implementation:
- **Date Input Conversion**: Changed from `type="date"` to `type="text"` with Persian date picker classes
- **Script Integration**: Added `ShamsiCalender/js/persianDatepicker.js` with proper initialization
- **CSS Enhancements**: Updated accordion body background to transparent
- **Table Styling**: Removed striped rows and header backgrounds for cleaner appearance
- **Button Layout**: Optimized filter buttons to fit properly within container constraints

#### Files Modified:
- **resources/views/pages/laboratory/results/grouped.blade.php**: Complete date picker integration and UI improvements
- **Enhanced User Experience**: Improved date selection with localized Dari calendar interface
- **Consistent Styling**: Aligned with other pages using Persian date picker implementation

#### Impact:
- **Localized Date Selection**: Users can now select dates using Dari calendar interface
- **Improved Interface**: Cleaner accordion tables without distracting background colors
- **Better User Experience**: Consistent date picker implementation across laboratory modules
- **Professional Appearance**: Optimized button layout and form styling

### 3. Language Translation Improvements (NEW)
**Implementation Date**: January 2025
**Contributor**: Development Team

#### Dari Language Translation Standardization:
- **Category Term Standardization**: Replaced all instances of "کتگوری" (English loanword) with "دسته‌بندی" (proper Dari term)
- **Comprehensive Translation Updates**: Updated 25+ category-related translations across the application
- **Test Category Translations**: Standardized laboratory test category terminology
- **Job Category Translations**: Updated job category terminology for consistency
- **Validation Messages**: Updated form validation messages to use proper Dari terms

#### Files Modified:
- **lang/dr/global.php**: Complete category terminology standardization
- **Translation Keys Updated**:
  - `categories` → "دسته‌بندی‌ها" (categories)
  - `category` → "دسته‌بندی" (category) 
  - `test_category` → "دسته‌بندی آزمایش" (test category)
  - `job_category` → "دسته‌بندی وظیفوی" (job category)
  - All related category management terms standardized

#### Impact:
- **Improved Localization**: Better Dari language support for Afghan users
- **Consistent Terminology**: Standardized category-related terms across all modules
- **Enhanced User Experience**: More natural language experience for Dari speakers
- **Professional Presentation**: Proper Persian/Dari terminology instead of English loanwords

### 4. Prescription Stock Management System (NEW)
**Implementation Date**: August 2025

#### New Models Created:
- **PrescriptionStock Model** (`app/Models/PrescriptionStock.php`)
  - Tracks current stock levels for medicines
  - Features: stock calculations, low stock alerts, expiry tracking
  - Helper methods: `isLowStock()`, `isOverstocked()`, `getStockStatus()`

- **Income Model** (`app/Models/Income.php`)
  - Tracks medicine stock additions (purchases, donations, returns)
  - Features: batch tracking, expiry date monitoring, supplier management
  - Helper methods: `isExpired()`, `isExpiringSoon()`, `getTotalValue()`

- **Outcome Model** (`app/Models/Outcome.php`)
  - Tracks medicine stock reductions (prescriptions, expirations, damages)
  - Features: prescription linking, patient tracking, audit trail
  - Helper methods: `isPrescriptionOutcome()`, `getOutcomeDescription()`

#### New Controllers:
- **PrescriptionStockController** - Stock management interface
- **IncomeController** - Stock addition management
- **OutcomeController** - Stock reduction management with reporting

#### Database Changes:
- **Migration**: `2025_08_20_084735_create_prescription_stock_management_tables.php`
- **New Tables**: `prescription_stocks`, `incomes`, `outcomes`
- **Features**: Foreign key constraints, indexes, soft deletes, audit trails

### 5. Nursing Management System (NEW)
**Implementation Date**: September 2025

#### New Models:
- **Nurse Model** (`app/Models/Nurse.php`)
- **NursingAssessment Model** (`app/Models/NursingAssessment.php`)
- **NurseNote Model** (`app/Models/NurseNote.php`)
- **MedicationAdministrationRecord Model** (`app/Models/MedicationAdministrationRecord.php`)
- **NutritionCare Model** (`app/Models/NutritionCare.php`)

#### New Controllers:
- **NurseController** - Nurse management
- **NursingAssessmentController** - Patient assessments
- **NurseNoteController** - Nursing notes
- **MedicationAdministrationRecordController** - Medication tracking
- **NutritionCareController** - Nutrition management

### 6. Physiotherapy Management System (NEW)
**Implementation Date**: September 2025

#### New Models:
- **PhysiotherapyType Model** (`app/Models/PhysiotherapyType.php`)
- **PhysiotherapyProcedure Model** (`app/Models/PhysiotherapyProcedure.php`)

#### New Controllers:
- **PhysiotherapyTypeController** - Procedure type management
- **PhysiotherapyProcedureController** - Procedure management
- **PhysiotherapyReportController** - Reporting system

### 7. Vital Signs Management (NEW)
**Implementation Date**: September 2025

#### New Models:
- **VitalSignType Model** (`app/Models/VitalSignType.php`)
- **VitalSign Model** (`app/Models/VitalSign.php`)
- **VitalSignSchedule Model** (`app/Models/VitalSignSchedule.php`)

#### New Controllers:
- **VitalSignController** - Vital signs tracking
- **VitalSignTypeController** - Vital sign type management
- **VitalSignScheduleController** - Scheduling system

### 8. Enhanced Reporting System
- **Operations Reports** - Comprehensive operation reporting
- **Anesthesia Reports** - Anesthesia procedure reports
- **ICU Reports** - Intensive care unit reporting
- **PACU Reports** - Post-anesthesia care reporting
- **Lab Reports** - Laboratory test reporting

## Current Application Modules

### Core Modules:
1. **Patient Management**
   - Patient registration and profiles
   - Medical history tracking
   - QR code generation for patient identification

2. **Appointment System**
   - Doctor appointments
   - Appointment scheduling
   - Status tracking (new, approved, rejected)

3. **Medical Procedures**
   - Operations management
   - Anesthesia procedures
   - ICU management
   - PACU (Post-Anesthesia Care Unit)

4. **Laboratory System**
   - Lab tests and results
   - Lab type management
   - Lab item tracking

5. **Pharmacy Management**
   - Medicine inventory
   - Prescription management
   - Stock tracking (NEW)

6. **Hospital Management**
   - Bed management
   - Room allocation
   - Floor management
   - Department organization

7. **User Management**
   - Role-based access control
   - Permission management
   - Multi-language support (English, Persian, Dari)

## Identified Issues & Problems

### 1. Current Linter Errors (25 Errors Found - January 2025)

#### Composer Package Issues:
- **composer.json**:
  - Line 24:9: 'laravelcollective/html' has been abandoned (Warning)

#### Language File Issues:
- **lang/ps/global.php** (12 duplicate key warnings):
  - Lines 76, 101, 12, 211, 215, 289, 353, 399, 419, 437, 441, 475: Duplicate array keys detected
  - **Impact**: Potential language translation conflicts in Pashto language support

- **lang/en/global.php** (12 duplicate key warnings):
  - Lines 76, 101, 198, 262, 329, 362, 380, 462, 481, 483, 484, 486: Duplicate array keys detected
  - **Impact**: Potential language translation conflicts in English language support

#### Previous Critical Issues (Resolved):
- **Date Method Calls**: 
  - `app/Models/Income.php` - Lines 93, 99: Incorrect date method calls (RESOLVED)
  - `resources/views/pages/nurse-notes/edit.blade.php` - Line 54: Unknown date::format() method (RESOLVED)

#### Controller Issues (Resolved):
- **PatientController.php**:
  - Line 66: Unknown method `withQueryString()` (RESOLVED)
  - Lines 98, 113, 157, 172: Duplicate array keys (RESOLVED)

#### View Issues (Resolved):
- **QrCode Class**: Unknown class usage in multiple views (RESOLVED)
- **Assignment Issues**: Same variable assignments in nursing assessment views (RESOLVED)

#### Return Value Issues (Resolved):
- **OutcomeController.php** - Line 214: Not all code paths return a value (RESOLVED)

### 2. Database Issues
- **Migration Dependencies**: Some migrations may have dependency issues
- **Foreign Key Constraints**: Potential orphaned records in some relationships
- **Index Optimization**: Some queries may need better indexing

### 3. Security Concerns
- **Input Validation**: Some forms may lack proper validation
- **Permission Checks**: Role-based access needs thorough testing
- **Data Sanitization**: User input needs proper sanitization

### 4. Performance Issues
- **N+1 Query Problems**: Some relationships may cause performance issues
- **Large Dataset Handling**: Pagination needed for large datasets
- **File Upload Optimization**: Image and document uploads need optimization

## Improvement

### 1. Immediate Fixes Required
- Fix all linter errors
- Implement proper date handling
- Resolve QrCode class issues
- Fix return value issues in controllers

### 2. Code Quality Improvements
- Implement comprehensive error handling
- Add proper logging mechanisms
- Improve code documentation
- Implement unit and feature tests

### 3. Security Enhancements
- Implement CSRF protection on all forms
- Add input validation middleware
- Implement rate limiting
- Add audit logging for sensitive operations

### 4. Performance Optimizations
- Implement database query optimization
- Add caching mechanisms
- Optimize file uploads
- Implement lazy loading for large datasets

### 5. User Experience Improvements
- Improve responsive design
- Add loading indicators
- Implement better error messages
- Add keyboard shortcuts

## Database Schema Summary

### Recent Migrations (2025-2026):
- `2026_01_03_100000_add_completion_to_income_type_enum.php` (NEW - January 2026)
- `2025_12_30_065134_create_sp_doctor_performance_dynamic_stored_procedure.php`
- `2025_12_30_061627_add_is_dentist_to_doctors_table.php`
- `2025_12_28_074344_create_patient_test_result_attachments_table.php`
- `2025_12_16_050520_add_dental_chart_id_to_dental_treatments_table.php`
- `2025_12_02_100008_create_dental_periodontal_measurements_table.php`
- `2025_12_02_100007_create_dental_chart_images_table.php`
- `2025_12_02_100006_create_dental_chart_measurements_table.php`
- `2025_12_02_100005_create_dental_charts_table.php`
- `2025_12_02_100004_create_dental_notes_table.php`
- `2025_12_02_100003_create_dental_xrays_table.php`
- `2025_12_02_100002_create_dental_treatments_table.php`
- `2025_12_02_100001_create_dental_examinations_table.php`
- `2025_12_02_100000_create_dentist_registrations_table.php`
- `2025_12_01_045216_add_user_id_to_doctors_table.php`
- `2025_11_17_062930_change_anesthesias_foreign_keys_to_doctors_table.php`
- `2025_11_16_095539_change_hospitalizations_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_15_060903_change_under_reviews_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_15_060053_change_consultation_comments_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_15_055445_change_advice_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_15_053116_add_doctor_id_foreign_key_to_patient_test_registrations_table.php`
- `2025_11_15_052514_change_prescriptions_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_15_044522_create_doctor_stored_procedures.php`
- `2025_11_15_043925_add_extra_fields_to_doctors_table.php`
- `2025_11_15_042447_change_appointments_doctor_id_foreign_key_to_doctors_table.php`
- `2025_11_12_093657_add_processed_by_to_appointments_table.php`
- `2025_11_05_100003_create_appointment_clinic_type_trigger.php`
- `2025_11_05_100002_create_get_doctors_by_clinic_type_stored_procedure.php`
- `2025_11_05_100001_add_clinic_type_to_appointments_table.php`
- `2025_11_05_100000_add_is_doctor_and_clinic_type_to_users_table.php`
- `2025_11_04_042121_update_sp_doctor_performance_dynamic_stored_procedure.php`
- `2025_10_27_101250_add_room_number_to_departments_table.php`
- `2025_10_27_065457_remove_medicine_type_and_disease_fields_from_medicines_table.php`
- `2025_10_26_061240_add_unique_constraint_to_lab_types_name.php`
- `2025_10_26_052247_add_assignment_fields_to_patient_test_registrations.php`
- `2025_10_26_050529_remove_section_branch_from_lab_types.php`
- `2025_10_25_051257_add_text_and_json_columns_to_patient_test_registrations.php`
- `2025_10_25_050941_fix_patient_test_registrations_lab_type_column.php`
- `2025_10_25_050832_clear_old_patient_test_registrations.php`
- `2025_10_25_044258_drop_parent_id_from_lab_types_table.php`
- `2025_10_25_043717_make_test_id_nullable_in_lab_test_parameters_table.php`
- `2025_10_23_061630_replace_lab_test_with_lab_type_in_patient_test_registrations.php`
- `2025_10_23_061617_add_category_id_to_lab_types_table.php`
- `2025_10_23_052407_add_lab_type_id_to_lab_test_parameters_table.php`
- `2025_10_23_042009_make_lab_parameter_id_nullable_in_patient_test_results_table.php`
- `2025_10_22_102300_drop_test_categories_table.php`
- `2025_10_22_102257_remove_testcategory_id_from_lab_test_parameters.php`
- `2025_10_22_101753_remove_category_id_from_lab_tests.php`
- `2025_10_22_100510_drop_old_lab_tables.php`
- `2025_10_21_084211_add_category_id_to_patient_test_registrations_table.php`
- `2025_10_20_081659_add_missing_columns_to_lab_test_parameters_table.php`
- `2025_10_20_073227_make_patient_id_nullable_in_patient_test_registrations.php`
- `2025_10_20_072209_add_polymorphic_columns_to_patient_test_registrations_table.php`
- `2025_10_19_054526_add_pharmacy_id_to_prescriptions_table.php`
- `2025_10_18_085748_add_category_id_to_departments_table.php`
- `2025_10_18_085725_add_category_id_to_users_table.php`
- `2025_10_18_074836_create_categories_table.php`
- `2025_10_16_061121_make_appointment_id_nullable_in_consultations_table.php`
- `2025_10_15_100316_make_medicine_type_id_nullable_in_prescription_items_table.php`
- `2025_10_13_074202_change_id_card_to_text_in_patients_table.php`
- `2025_09_16_061450_create_nursing_assessments_table.php`
- `2025_09_16_051538_add_nurse_id_to_nutrition_cares_table.php`
- `2025_09_16_050329_create_nutrition_cares_table.php`
- `2025_09_15_060259_create_vital_sign_schedules_table.php`
- `2025_09_15_060256_create_vital_signs_table.php`
- `2025_09_15_060254_create_vital_sign_types_table.php`
- `2025_09_15_043248_create_medication_administration_times_table.php`
- `2025_09_15_043241_create_medication_administration_records_table.php`
- `2025_09_14_095018_add_note_column_to_nurse_notes_table.php`
- `2025_09_14_092456_create_nurse_notes_table.php`
- `2025_09_14_060053_create_diabetes_charts_table.php`
- `2025_09_14_052928_add_branch_id_to_nurses_table.php`
- `2025_09_14_050137_add_user_id_to_nurses_table.php`
- `2025_09_14_044542_create_nurses_table.php`
- `2025_09_07_063935_create_jobs_table.php`
- `2025_09_07_040840_create_spiete_backups_table.php`
- `2025_09_03_000000_create_physiotherapy_procedure_reviews_table.php`
- `2025_09_01_053940_create_physiotherapy_procedures_table.php`
- `2025_09_01_053933_create_physiotherapy_types_table.php`
- `2025_08_20_084735_create_prescription_stock_management_tables.php`
- `2025_08_19_044751_create_pharmacies_table.php`
- `2025_08_17_082749_create_prescription_alternative_items_table.php`
- `2025_08_16_045531_create_militery_types_table.php`

### Recent Changes & Improvements (December 2024 - January 2026):

#### 1. Modern Login Page Design Implementation (NEW)
**Implementation Date**: January 2025
**Contributor**: Development Team

##### Login Page Redesign:
- **Modern UI Framework**: Replaced Bootstrap-based login with Tailwind CSS for contemporary design
- **Split-Screen Layout**: Implemented professional split-screen design with hospital image and login form
- **Glassmorphism Effects**: Added backdrop blur and transparency effects for modern visual appeal
- **Responsive Design**: Enhanced mobile responsiveness with adaptive layout
- **Enhanced UX**: Improved user experience with better form styling and visual hierarchy

##### Technical Implementation:
- **Tailwind CSS Integration**: Added Tailwind CSS CDN with custom configuration
- **Custom Color Scheme**: Implemented primary color (#13a4ec) and background themes
- **Font Optimization**: Integrated Inter font family for better typography
- **Dark Mode Support**: Added dark mode classes for enhanced user experience
- **Icon Integration**: Enhanced password toggle with proper SVG icons

##### Preserved Functionality:
- **Laravel Integration**: Maintained all Laravel authentication features
- **CSRF Protection**: Preserved @csrf token for security
- **Form Validation**: Kept all error handling with @error directives
- **Localization**: Maintained all {{ localize() }} calls for multi-language support
- **RTL/LTR Support**: Preserved language direction switching
- **Custom Fonts**: Maintained custom font loading for different languages
- **Password Toggle**: Enhanced password visibility toggle with proper icon switching
- **Remember Me**: Preserved remember me checkbox functionality

##### Files Modified:
- **resources/views/auth/login.blade.php**: Complete redesign with modern Tailwind CSS implementation
- **Enhanced JavaScript**: Improved password toggle functionality with dynamic icon switching
- **Responsive Layout**: Mobile-first design approach with proper breakpoints

#### 2. Appointment System Enhancements (NEW)
**Implementation Date**: January 2025
**Contributor**: Development Team

##### Patient Controller Improvements:
- **Enhanced Appointment Creation Logic** (`app/Http/Controllers/PatientController.php`):
  - **Department Selection Integration**: Added dynamic department selection in patient creation
  - **Doctor Loading**: Implemented dynamic doctor loading based on selected department
  - **Appointment Token Printing**: Refactored appointment token printing logic to use `department_id` directly from appointment
  - **Null-Safe Operations**: Updated appointment and patient views to use null-safe operator for doctor access
  - **Select2 Integration**: Enhanced Select2 initialization for improved maintainability in patient creation view

##### Appointment Management Features:
- **Appointment Creation Process**: Refactored appointment creation process in PatientController
- **Department-Based Doctor Loading**: Implemented department selection and dynamic doctor loading
- **Enhanced Views**: Updated appointment and patient views with improved functionality
- **Notification System**: Integrated appointment notifications with department-based routing

#### 2. Frontend JavaScript Enhancements (NEW)
**Implementation Date**: January 2025
**Contributor**: Development Team

##### Forms-Pickers.js Improvements:
- **Flatpickr Integration**: Enhanced flatpickr initialization and error handling
- **Library Availability Checks**: Added checks for moment.js and bootstrap-daterangepicker availability
- **Error Handling**: Implemented comprehensive error handling for missing dependencies
- **DOM Ready State**: Added proper DOM ready state checking before initialization

##### Select2 Integration Enhancements:
- **Improved Initialization**: Enhanced Select2 integration for dropdowns in patient creation view
- **Error Prevention**: Added checks for library availability before initialization
- **Maintainability**: Refactored Select2 initialization for improved maintainability

#### 3. Internationalization (i18n) Configuration Updates (NEW)
**Implementation Date**: January 2025
**Contributor**: Development Team

##### i18n Configuration Fixes:
- **LoadPath Correction**: Fixed loadPath in i18n configuration to include leading slash for correct JSON file path
- **Language File Updates**: Enhanced language file structure and organization
- **Multi-language Support**: Improved support for English, Dari, and Pashto languages

#### 4. Date Format Standardization (CONTINUED)
**Implementation Date**: December 2024 - January 2025
**Contributor**: Development Team

##### ICU Module Date Conversions:
- **ICU Show Page** (`resources/views/pages/icus/show.blade.php`):
  - Converted all table date displays to Dari format using Verta
  - **Prescription Table**: Changed `{{ $pres_list->created_at }}` to `{{ verta($pres_list->created_at)->format('Y-m-d H:i') }}`
  - **Advice Table**: Changed `{{ $advice->created_at }}` to `{{ verta($advice->created_at)->format('Y-m-d H:i') }}`
  - **Procedure Table**: Changed `{{ $procedure->created_at }}` to `{{ verta($procedure->created_at)->format('Y-m-d H:i') }}`
  - **Daily Progress Table**: Changed `{{ $progress->created_at }}` to `{{ verta($progress->created_at)->format('Y-m-d H:i') }}`
  - **Death Date Input**: Changed from `type="date"` to `type="text"` with `class="form-control datepicker_dari"`

##### Print Documents Date Conversions:
- **Transfer Sheet** (`resources/views/pages/icus/print_move_card.blade.php`):
  - **Procedure Dates**: Converted `{{ $procedure->created_at->format('Y-m-d') }}` to `{{ verta($procedure->created_at)->format('Y-m-d') }}`
  - **Primary Diagnosis Dates**: Converted `{{ $diagnose->created_at->format('Y-m-d') }}` to `{{ verta($diagnose->created_at)->format('Y-m-d') }}`
  - **Final Diagnosis Dates**: Converted `{{ $diagnose->created_at->format('Y-m-d') }}` to `{{ verta($diagnose->created_at)->format('Y-m-d') }}`
  - **Operation Dates**: Converted `{{ $operation->created_at->format('Y-m-d') }}` to `{{ verta($operation->created_at)->format('Y-m-d') }}`
  - **Consultation Dates**: Converted `{{ $consultation->created_at->format('Y-m-d') }}` to `{{ verta($consultation->created_at)->format('Y-m-d') }}`
  - **Comment Dates**: Converted `{{ $comment->created_at->format('Y-m-d') }}` to `{{ verta($comment->created_at)->format('Y-m-d') }}`

- **Death Summary** (`resources/views/pages/icus/print_death_card.blade.php`):
  - **Admission Date**: Changed `{{ $icu->appointment->created_at }}` to `{{ verta($icu->appointment->created_at)->format('Y-m-d H:i') }}`
  - **ICU Admission Date**: Changed `{{ $icu->created_at }}` to `{{ verta($icu->created_at)->format('Y-m-d H:i') }}`
  - **Procedure Dates**: Changed `{{ $procedure->created_at->format('Y-m-d') }}` to `{{ verta($procedure->created_at)->format('Y-m-d') }}`
  - **Primary Diagnosis Dates**: Changed `{{ $diagnose->created_at->format('Y-m-d') }}` to `{{ verta($diagnose->created_at)->format('Y-m-d') }}`
  - **Final Diagnosis Dates**: Changed `{{ $diagnose->created_at->format('Y-m-d') }}` to `{{ verta($diagnose->created_at)->format('Y-m-d') }}`

##### Lab Module Improvements:
- **Lab Edit Form** (`resources/views/pages/labs/edit.blade.php`):
  - **Fixed Syntax Error**: Removed incorrect `</textarea>` tag from file input field
  - **Improved Button Structure**: Fixed malformed button layout with proper Bootstrap classes
  - **Enhanced Form Layout**: Used `d-flex gap-2` for proper spacing between buttons
  - **Added File Display**: Added current file display with `@if($lab->result_file)` condition

##### Appointment Module Enhancements:
- **Appointment Edit Form** (`resources/views/pages/appointments/edit.blade.php`):
  - **Dari Date Picker Integration**: Changed date input from `type="date"` to `type="text"` with `class="form-control datepicker_dari"`
  - **Persian Date Picker Script**: Added `@push('custom-js')` section with Persian date picker initialization
  - **JavaScript Configuration**: Implemented proper Persian date picker with `formatDate: 'YYYY-MM-DD'` and calendar settings
  - **Form Validation**: Enhanced form validation with proper error handling for date inputs

#### 5. Sulaiman Qasimi Enhanced Contributions:

##### Custom Router Package Updates:
- **Package Version**: Updated to latest development version
- **Enhanced Routing**: Improved custom routing functionality for medical application
- **Performance Optimization**: Optimized routing performance for large-scale medical data handling
- **Security Enhancements**: Added additional security measures to custom router package

##### Technical Improvements:
- **Code Quality**: Enhanced code quality standards in custom router implementation
- **Documentation**: Improved package documentation and usage examples
- **Integration**: Streamlined integration process with Laravel 10 medical application
- **Testing**: Added comprehensive testing suite for custom router functionality

### Recent Git Commits & Changes (January 2025 - January 2026):

#### Latest Commits (Recent 14):
1. **Latest (January 2026)**: Users management interface enhancements - Fixed statistics cards accuracy and added per-page selector for pagination
2. **Previous (January 2026)**: Income management system enhancements - Added 'completion' income type and made purchase_price optional
3. **Previous (January 2025)**: Laboratory results interface enhancements with Dari date picker integration and UI improvements
3. **Previous**: Modern login page design implementation with Tailwind CSS and responsive layout
3. **9551bfc**: Refactor appointment token printing logic to use department_id directly from appointment
4. **deff62a**: Update appointment and patient views to use null-safe operator for doctor access
5. **54a8319**: Update appointment creation logic in PatientController and enhance appointment view
6. **cfdd504**: Refactor Select2 initialization for improved maintainability in patient creation view
7. **e93ebd6**: Add checks for moment.js and bootstrap-daterangepicker availability in forms-pickers.js
8. **983bfcf**: Enhance flatpickr initialization and error handling in forms-pickers.js
9. **0f8439b**: Fix loadPath in i18n configuration to include leading slash for correct JSON file path
10. **4c84296**: Enhance Select2 integration for dropdowns in patient creation view
11. **a78b5e2**: Implement department selection and dynamic doctor loading in PatientController
12. **Previous**: Language translation standardization with Dari terminology improvements

#### Key Changes Implemented:
- **Users Management Enhancement (January 2026)**: Fixed statistics cards to display accurate counts using full collection, added per-page selector (10, 20, 50, 100) for pagination control
- **Income Management Enhancement (January 2026)**: Added 'completion' income type, extended database enum, made purchase_price optional
- **Database Schema Updates**: New migration for income_type enum extension with proper rollback support
- **Form Flexibility**: Improved income creation form with optional purchase price field
- **Laboratory Results Interface**: Enhanced grouped results page with Dari date picker integration and improved UI
- **Date Picker Standardization**: Converted HTML5 date inputs to Persian calendar interface for better localization
- **Interface Optimization**: Removed background colors from accordion tables and optimized button layouts
- **Modern Login Design**: Complete redesign of login page with Tailwind CSS, split-screen layout, and glassmorphism effects
- **Enhanced User Experience**: Improved login interface with responsive design, dark mode support, and modern typography
- **Appointment System**: Complete refactoring of appointment creation and management
- **Department Integration**: Dynamic department selection and doctor loading
- **Frontend Improvements**: Enhanced JavaScript error handling and library checks
- **i18n Configuration**: Fixed internationalization path issues
- **Select2 Integration**: Improved dropdown functionality and maintainability

### Recent Error Fixes (2024-2025):

#### Critical Date-Related Errors Fixed:

##### 1. ICU Show Page Date Display Errors (December 2024)
**Error**: Tables displaying Gregorian dates instead of Persian/Dari dates
**Files Affected**: `resources/views/pages/icus/show.blade.php`
**Specific Errors**:
- **Line 981**: `{{ $pres_list->created_at }}` - Prescription table showing Gregorian dates
- **Line 1086**: `{{ $advice->created_at }}` - Advice table showing Gregorian dates  
- **Line 1352**: `{{ $procedure->created_at }}` - Procedure table showing Gregorian dates
- **Line 1638**: `{{ $progress->created_at }}` - Daily progress table showing Gregorian dates
**Solution**: Converted all to `{{ verta($variable->created_at)->format('Y-m-d H:i') }}`
**Status**: ✅ RESOLVED

##### 2. Print Documents Date Format Errors (December 2024)
**Error**: Print documents displaying inconsistent date formats
**Files Affected**: 
- `resources/views/pages/icus/print_move_card.blade.php`
- `resources/views/pages/icus/print_death_card.blade.php`

**Transfer Sheet Errors**:
- **Line 100**: `{{ $procedure->created_at->format('Y-m-d') }}` - Procedure dates in Gregorian
- **Line 151**: `{{ $diagnose->created_at->format('Y-m-d') }}` - Primary diagnosis dates in Gregorian
- **Line 164**: `{{ $diagnose->created_at->format('Y-m-d') }}` - Final diagnosis dates in Gregorian
- **Line 197**: `{{ $operation->created_at->format('Y-m-d') }}` - Operation dates in Gregorian
- **Line 244**: `{{ $consultation->created_at->format('Y-m-d') }}` - Consultation dates in Gregorian
- **Line 257**: `{{ $comment->created_at->format('Y-m-d') }}` - Comment dates in Gregorian

**Death Summary Errors**:
- **Line 79**: `{{ $icu->appointment->created_at }}` - Admission date in Gregorian
- **Line 84**: `{{ $icu->created_at }}` - ICU admission date in Gregorian
- **Line 105**: `{{ $procedure->created_at->format('Y-m-d') }}` - Procedure dates in Gregorian
- **Line 147**: `{{ $diagnose->created_at->format('Y-m-d') }}` - Primary diagnosis dates in Gregorian
- **Line 156**: `{{ $diagnose->created_at->format('Y-m-d') }}` - Final diagnosis dates in Gregorian

**Solution**: Converted all to `{{ verta($variable->created_at)->format('Y-m-d') }}` or `{{ verta($variable->created_at)->format('Y-m-d H:i') }}`
**Status**: ✅ RESOLVED

##### 3. Lab Edit Form Critical Errors (December 2024)
**Error**: Save button not working due to syntax errors
**File Affected**: `resources/views/pages/labs/edit.blade.php`
**Specific Errors**:
- **Line 76**: Syntax error - `</textarea>` tag incorrectly placed in file input field
- **Lines 80-83**: Malformed button structure causing form submission failure
- **Missing File Display**: No indication of current file when editing
**Solution**: 
- Removed incorrect `</textarea>` tag
- Fixed button structure with proper Bootstrap classes
- Added current file display with conditional statement
**Status**: ✅ RESOLVED

##### 4. Appointment Date Picker Integration Errors (December 2024)
**Error**: Date input using HTML5 date picker instead of Persian calendar
**File Affected**: `resources/views/pages/appointments/edit.blade.php`
**Specific Errors**:
- **Line 135**: `type="date"` input causing Gregorian date selection
- **Missing Persian Date Picker**: No JavaScript initialization for Dari calendar
- **Date Format Conflicts**: Existing date values causing "invalid date" errors
**Solution**:
- Changed to `type="text"` with `class="form-control datepicker_dari"`
- Added Persian date picker script initialization
- Implemented proper date clearing before initialization
**Status**: ✅ RESOLVED

##### 5. Death Date Input Field Error (December 2024)
**Error**: Death date input using HTML5 date picker in discharge modal
**File Affected**: `resources/views/pages/icus/show.blade.php`
**Specific Error**:
- **Line 1991**: `type="date"` input for death date selection
**Solution**: Changed to `type="text"` with `class="form-control datepicker_dari"`
**Status**: ✅ RESOLVED

##### 6. Income Type Enum Database Error (January 2026)
**Error**: `SQLSTATE[01000]: Warning: 1265 Data truncated for column 'income_type' at row 1`
**Error Message**: Database rejecting 'completion' value for income_type column
**Root Cause**: ENUM column definition didn't include 'completion' as valid value
**Files Affected**: 
- `app/Http/Controllers/IncomeController.php`
- `resources/views/pages/incomes/create.blade.php`
- Database `incomes` table schema

**Specific Errors**:
- **Database Schema**: ENUM column only allowed: `['purchase', 'return', 'donation', 'transfer', 'adjustment']`
- **Controller**: Validation and type array included 'completion' but database rejected it
- **Form Submission**: Users unable to create income records with 'completion' type

**Solution**: 
- Created migration `2026_01_03_100000_add_completion_to_income_type_enum.php`
- Extended ENUM column to include 'completion': `['purchase', 'return', 'donation', 'transfer', 'adjustment', 'completion']`
- Updated controller validation rules to match database schema
- Ensured proper rollback support in migration down method
- Made purchase_price field optional for better form flexibility

**Status**: ✅ RESOLVED

##### 7. Users Statistics Cards Inaccurate Count Issue (January 2026)
**Error**: Statistics cards displaying incorrect user counts when total users exceeded pagination limit
**Error Message**: Statistics showing only paginated page count instead of total database count
**Root Cause**: Statistics were calculated using paginated collection (`$users`) instead of full collection
**Files Affected**: 
- `app/Http/Controllers/UserController.php`
- `resources/views/pages/users/index.blade.php`

**Specific Errors**:
- **Active Users Count**: Showing only active users from current page (e.g., 20 instead of 21)
- **Deactive Users Count**: Showing only deactive users from current page
- **Total Users Count**: Showing only paginated count instead of total database count
- **New Users Count**: Showing only new users from current page instead of all new users

**Solution**: 
- Separated statistics calculation from pagination in controller
- Created full collection before pagination: `$allUsers = $query->get()`
- Updated all statistics cards in view to use `$allUsers` instead of `$users`
- Added per-page selector dropdown for better user control
- Implemented JavaScript handler for per-page selection with URL parameter preservation

**Status**: ✅ RESOLVED

##### 8. Hospitalizations Accordion Collapse Functionality Issue (January 2026)
**Error**: Accordion buttons not working on hospitalizations show page - accordions not expanding/collapsing when clicked
**Error Message**: Accordion collapse functionality not responding to user clicks
**Root Cause**: Bootstrap 5 collapse instances not being properly initialized, possibly due to conflicts with hijri/bootstrap.js (Bootstrap 4) loading after Bootstrap 5
**Files Affected**: 
- `resources/views/pages/hospitalizations/show.blade.php`

**Specific Errors**:
- **Accordion Buttons**: Multiple accordion buttons with `data-bs-toggle="collapse"` attributes not functioning
- **Affected Accordions**: 
  - Visits Accordion (`#visitsAccordion`)
  - Vital Signs Accordion (`#vitalSignsAccordion`)
  - Diabetes Charts Accordion (`#diabetesChartsAccordion`)
  - Nursing Notes Accordion (`#nursingNotesAccordion`)
  - Medication Records Accordion (`#medicationRecordsAccordion`)
  - Nutrition Care Accordion (`#nutritionCareAccordion`)
- **JavaScript Syntax Error**: Missing `var` declaration on line 1063 causing "Unexpected token ','" error
- **Bootstrap Initialization**: Bootstrap 5 Collapse instances not being created for accordion elements

**Solution**: 
- Fixed missing `var` declaration for `typeOption` variable in `addRow()` function (line 1063)
- Identified that Bootstrap 5 collapse functionality requires explicit initialization when Bootstrap 4 (hijri/bootstrap.js) is loaded after Bootstrap 5
- Added Bootstrap 5 Collapse initialization code to ensure all accordion collapse elements are properly initialized
- Implementation waits for DOM and Bootstrap to be fully loaded before initializing collapse instances

**Technical Details**:
- **Bootstrap Version Conflict**: hijri/bootstrap.js (Bootstrap 4) loaded after main Bootstrap 5 may interfere with auto-initialization
- **Manual Initialization Required**: Bootstrap 5 collapse elements need explicit initialization when conflicts exist
- **Initialization Strategy**: Wait for DOM ready state and Bootstrap availability, then create Collapse instances for all `.accordion-collapse` elements

**Status**: ✅ RESOLVED

#### Other Fixed Issues:
- **Camera Integration Errors**: Resolved camera functionality issues in patient registration and medical
- **Print Functionality**: Resolved printing issues in reports and medical documents
- **Registration System**: Fixed user and patient registration validation errors
- **Form Field Validation**: Corrected field validation errors in various forms
- **Nurse Selection Issues**: Fixed nurse selection and assignment functionality
- **Seeder Errors**: Resolved database seeder errors and data population issues
## Conclusion

The medical database application has undergone significant development with the addition of comprehensive stock management, nursing systems, enhanced reporting capabilities, and recent date format standardization. The project has benefited from contributions by Sulaiman Qasimi (custom router package development and integration) and Mohammad Rafi 10 (database seeding and geographic data management), along with recent improvements in date handling and form functionality.

### Recent Achievements (December 2024 - January 2025):

#### January 2026 Achievements:
- **Users Management Interface Enhancement**: Successfully fixed statistics cards to display accurate user counts regardless of pagination
- **Pagination Control**: Implemented per-page selector dropdown (10, 20, 50, 100) for flexible data viewing
- **Data Accuracy**: Separated statistics calculation from pagination to ensure correct counts for all users
- **User Experience**: Enhanced pagination footer with responsive design and dynamic per-page selection
- **Income Management System Enhancement**: Successfully added 'completion' (اکمال) as new income type option
- **Database Schema Update**: Extended income_type ENUM column to support completion type
- **Form Flexibility**: Made purchase_price field optional for improved user experience
- **Validation Updates**: Updated controller validation rules to support new income type and optional purchase price
- **Accordion Functionality Fix**: Resolved accordion collapse issues on hospitalizations show page by fixing JavaScript syntax errors and ensuring proper Bootstrap 5 initialization
- **Bootstrap Compatibility**: Addressed Bootstrap version conflicts between Bootstrap 5 and hijri/bootstrap.js (Bootstrap 4) for proper accordion functionality

#### January 2025 Achievements:
- **Laboratory Results Interface Enhancement**: Successfully implemented Dari date picker integration in grouped results page
- **Date Picker Standardization**: Converted HTML5 date inputs to Persian calendar interface for better localization
- **Interface Optimization**: Removed background colors from accordion tables and optimized button layouts
- **Language Translation Standardization**: Successfully standardized Dari language translations, replacing English loanwords with proper Persian/Dari terminology
- **Category Terminology Improvement**: Updated 25+ category-related translations across all modules for better localization
- **Modern Login Page Design**: Successfully implemented contemporary login page with Tailwind CSS, split-screen layout, and glassmorphism effects
- **Enhanced User Experience**: Improved login interface with responsive design, dark mode support, and modern typography
- **Appointment System Enhancement**: Successfully implemented dynamic department selection and doctor loading
- **Frontend JavaScript Improvements**: Enhanced forms-pickers.js with better error handling and library availability checks
- **Select2 Integration**: Improved Select2 initialization for better maintainability and user experience
- **i18n Configuration**: Fixed loadPath issues in internationalization configuration
- **Patient Management**: Enhanced patient creation process with department-based appointment creation

#### December 2024 Achievements:
- **Date Format Standardization**: Successfully converted all date displays to Persian/Dari calendar format using Verta
- **ICU Module Enhancement**: Improved date handling across all ICU-related tables and print documents
- **Form Functionality**: Fixed critical form issues in lab and appointment modules
- **Print Document Consistency**: Standardized date formats in transfer sheets and death summaries
- **User Experience**: Enhanced date picker functionality with Persian calendar integration

### Key Contributors:
- **Sulaiman Qasimi**: 
  - Repository: https://github.com/sulaimanQasimi/mod-health-app
  - Custom router package development, integration, and performance optimization
  - Primary repository management and release coordination
- **Mohammad Rafi 10**: 
  - Repository: https://github.com/mohhamdrafi10/mod-health-app
  - Database seeding, user management, geographic data implementation, and recent date format improvements
  - Collaborative repository synchronization and feature development
- **Development Team**: Date standardization, form fixes, and user interface enhancements

### Current Status (January 2026):
The application has significantly improved in terms of:
- **Users Management**: Fixed statistics cards accuracy to display correct user counts, added per-page selector for flexible pagination control
- **Data Accuracy**: Statistics now reflect actual database counts instead of paginated page data
- **Pagination Control**: Users can select display options (10, 20, 50, 100 items per page) for optimized viewing
- **Income Management**: Enhanced income tracking with new 'completion' type and optional purchase price field
- **Database Schema**: Extended income_type enum to support additional income categorization
- **Form Flexibility**: Improved income creation form with optional fields for better user experience
- **Accordion Functionality**: Fixed accordion collapse issues on hospitalizations page, ensuring all accordion sections work properly
- **Bootstrap Compatibility**: Resolved Bootstrap version conflicts to ensure proper UI component functionality
- **Laboratory Interface**: Enhanced grouped results page with Dari date picker integration and optimized UI
- **Date Picker Consistency**: Standardized Persian calendar implementation across laboratory modules
- **Interface Cleanliness**: Removed distracting background colors from accordion tables for better user experience
- **Button Layout Optimization**: Fixed filter button layout issues for proper container fitting
- **Language Localization**: Standardized Dari language translations with proper Persian terminology
- **Category Management**: Improved category-related terminology across all modules
- **Modern User Interface**: Implemented contemporary login page design with Tailwind CSS and responsive layout
- **Enhanced User Experience**: Added glassmorphism effects, dark mode support, and modern typography
- **Appointment Management**: Enhanced with dynamic department selection and doctor loading
- **Frontend Stability**: Improved JavaScript error handling and library availability checks
- **Date Consistency**: All date displays now use Persian/Dari calendar format
- **Form Functionality**: Resolved critical form submission issues
- **Print Documents**: Standardized date formats across all medical documents
- **User Interface**: Enhanced date picker functionality and Select2 integration
- **Internationalization**: Fixed i18n configuration issues and improved language support

### Current Issues Requiring Attention:
1. **Language File Duplicates**: 24 duplicate key warnings in language files (English and Pashto)
2. **Abandoned Package**: laravelcollective/html package marked as abandoned
3. **Language File Organization**: Need to clean up duplicate keys in global.php files

### Priority Actions:
1. **High Priority**: 
   - Fix duplicate keys in language files (lang/en/global.php and lang/ps/global.php)
   - Replace abandoned laravelcollective/html package with alternative
   - Clean up language file organization
2. **Medium Priority**: 
   - Implement comprehensive testing for new appointment functionality
   - Performance optimization for large datasets
3. **Low Priority**: 
   - Additional UI improvements and user experience enhancements
   - Advanced reporting features

The application is now more functional with improved income management, enhanced appointment management, enhanced frontend stability, better date handling, and standardized Dari language translations, making it more suitable for production deployment with proper Persian/Dari calendar support, dynamic department-based workflows, comprehensive income tracking, and improved localization for Afghan users.
