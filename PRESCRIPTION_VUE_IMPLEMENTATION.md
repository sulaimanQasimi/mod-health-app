# Prescription Section Vue.js Implementation

## Overview
This implementation provides a fully Ajax-supported prescription section using Vue.js 3 for the appointment show page. The prescription section now includes dynamic form handling, real-time updates, and improved user experience similar to the lab section.

## Files Created/Modified

### New Files Created:
1. **`app/Http/Controllers/PrescriptionAjaxController.php`** - New controller handling all Ajax operations for prescription functionality
2. **`public/assets/js/vue/components/PrescriptionSection.vue`** - Vue.js component for the prescription section
3. **`public/assets/js/vue/prescription-app.js`** - Vue.js application entry point for prescription section

### Files Modified:
1. **`routes/web.php`** - Added new Ajax routes for prescription operations
2. **`resources/views/pages/appointments/show.blade.php`** - Replaced static prescription section with Vue component

## Features Implemented

### Vue.js Component Features:
- **Dynamic Medicine Type Loading** - Loads medicine types via Ajax
- **Cascading Dropdowns** - Medicines load based on selected type
- **Multi-item Prescription Forms** - Add/remove prescription items dynamically
- **Real-time Prescription List** - Shows current prescriptions for the appointment
- **Modal Management** - Create prescription and view prescription items modals
- **Permission-based Actions** - Add/Edit/Delete buttons based on user permissions
- **Status Management** - Track prescription and item delivery status

### Ajax Controller Features:
- **`getMedicineTypes()`** - Fetch all medicine types
- **`getMedicinesByType($typeId)`** - Get medicines by type
- **`getMedicineUsageTypes()`** - Get medicine usage types
- **`storePrescription()`** - Create new prescription with validation
- **`getAppointmentPrescriptions($appointmentId)`** - Get all prescriptions for an appointment
- **`getPrescriptionItems($prescriptionId)`** - Get detailed prescription items
- **`updatePrescriptionStatus()`** - Update prescription completion status
- **`updatePrescriptionItemStatus()`** - Update individual item delivery status
- **`deletePrescription($prescriptionId)`** - Delete prescription
- **`deletePrescriptionItem($itemId)`** - Delete prescription item

## API Endpoints

### Prescription Ajax Routes:
```
GET    /prescription-ajax/medicine-types              - Get all medicine types
GET    /prescription-ajax/medicines-by-type/{typeId}  - Get medicines by type
GET    /prescription-ajax/medicine-usage-types        - Get medicine usage types
POST   /prescription-ajax/store                        - Create new prescription
GET    /prescription-ajax/appointment-prescriptions/{appointmentId} - Get appointment prescriptions
GET    /prescription-ajax/prescription-items/{prescriptionId} - Get prescription items
PUT    /prescription-ajax/update-status/{prescriptionId} - Update prescription status
POST   /prescription-ajax/update-item-status/{itemId} - Update item status
DELETE /prescription-ajax/delete/{prescriptionId}     - Delete prescription
DELETE /prescription-ajax/delete-item/{itemId}        - Delete prescription item
```

## Vue.js Component Structure

### PrescriptionSection.vue Features:
- **Reactive Data Management** - All prescription data is reactive
- **Dynamic Form Handling** - Add/remove prescription items dynamically
- **Cascading Dropdowns** - Medicine selection based on type
- **Modal Management** - Create and view modals with proper state management
- **Permission-based UI** - Show/hide buttons based on user permissions
- **Status Updates** - Real-time status updates for prescriptions and items
- **Error Handling** - Comprehensive error handling with user feedback

### Key Methods:
- `loadMedicineTypes()` - Load medicine types
- `loadMedicinesByType(index)` - Load medicines for specific type
- `addPrescriptionItem()` - Add new prescription item row
- `removePrescriptionItem(index)` - Remove prescription item row
- `submitPrescription()` - Submit prescription form
- `viewPrescriptionItems(prescriptionId)` - View prescription details
- `markAsDelivered(itemId)` - Mark item as delivered
- `markAsNotDelivered(itemId)` - Mark item as not delivered
- `deletePrescription(prescriptionId)` - Delete prescription

## Integration with Existing System

### Permission Integration:
- Uses existing Laravel permission system
- `canAddPrescription` - Controls add button visibility
- `canEditPrescription` - Controls edit functionality
- `canDeletePrescription` - Controls delete functionality

### Notification Integration:
- Sends notifications via `SendNewPrescriptionNotification` job
- Integrates with existing notification system

### Data Relationships:
- Maintains all existing relationships (Patient, Doctor, Appointment, etc.)
- Supports hospitalization, under review, and ICU contexts
- Preserves existing data structure

## Usage

### In Blade Template:
```php
<!-- Prescription Section Vue Component -->
<div id="prescription-section-container" 
     data-appointment='@json($appointment)'
     data-permissions='@json([
         "canAddPrescription" => auth()->user()->can("add-prescription"),
         "canEditPrescription" => auth()->user()->can("edit-prescriptions"),
         "canDeletePrescription" => auth()->user()->can("delete-prescriptions")
     ])'>
    <!-- Fallback content while Vue loads -->
</div>
```

### Vue.js Initialization:
```javascript
// Auto-initialize if prescription section container exists
document.addEventListener('DOMContentLoaded', function() {
    const prescriptionContainer = document.getElementById('prescription-section-container');
    if (prescriptionContainer) {
        const appointmentData = JSON.parse(prescriptionContainer.dataset.appointment || '{}');
        const permissions = JSON.parse(prescriptionContainer.dataset.permissions || '{}');
        
        const app = createPrescriptionApp(appointmentData, permissions);
        app.mount('#prescription-section-container');
    }
});
```

## Benefits

1. **Improved User Experience** - Dynamic form handling and real-time updates
2. **Better Performance** - Ajax-based operations reduce page reloads
3. **Enhanced Functionality** - Multi-item prescription forms with dynamic medicine selection
4. **Consistent UI** - Matches the existing lab section implementation
5. **Permission-based Access** - Proper permission handling for all operations
6. **Error Handling** - Comprehensive error handling and user feedback
7. **Maintainable Code** - Clean separation of concerns with Vue.js components

## Dependencies

- Vue.js 3
- Laravel 8+
- Existing permission system
- Existing notification system
- Bootstrap 5 (for styling)

## Future Enhancements

1. **Bulk Operations** - Add bulk prescription operations
2. **Advanced Filtering** - Add filtering and search capabilities
3. **Export Functionality** - Add prescription export features
4. **Print Integration** - Add prescription printing capabilities
5. **Mobile Optimization** - Enhance mobile responsiveness
