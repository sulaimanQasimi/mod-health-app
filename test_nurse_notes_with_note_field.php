<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Nurse Notes with Note Field Implementation...\n\n";

// Test 1: Check if table structure is correct
echo "1. Testing database table structure: ";
try {
    $columns = DB::select("SHOW COLUMNS FROM nurse_notes");
    $hasTimeAm = false;
    $hasTimePm = false;
    $hasNote = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'time_am' && $column->Type === 'time') {
            $hasTimeAm = true;
        }
        if ($column->Field === 'time_pm' && $column->Type === 'time') {
            $hasTimePm = true;
        }
        if ($column->Field === 'note' && $column->Type === 'text') {
            $hasNote = true;
        }
    }
    
    if ($hasTimeAm && $hasTimePm && $hasNote) {
        echo "✓ PASS (All required fields exist)\n";
    } else {
        echo "✗ FAIL (Table structure incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 2: Check if model fillable fields are updated
echo "2. Testing model fillable fields: ";
try {
    $note = new App\Models\NurseNote();
    $fillable = $note->getFillable();
    
    $hasTimeAm = in_array('time_am', $fillable);
    $hasTimePm = in_array('time_pm', $fillable);
    $hasNote = in_array('note', $fillable);
    
    if ($hasTimeAm && $hasTimePm && $hasNote) {
        echo "✓ PASS (Model fillable fields updated)\n";
    } else {
        echo "✗ FAIL (Model fillable fields incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 3: Check if form request validation includes note field
echo "3. Testing form request validation: ";
try {
    $storeRequest = new App\Http\Requests\StoreNurseNoteRequest();
    $rules = $storeRequest->rules();
    
    $hasTimeAmRule = isset($rules['time_am']) && str_contains($rules['time_am'], 'date_format:H:i');
    $hasTimePmRule = isset($rules['time_pm']) && str_contains($rules['time_pm'], 'date_format:H:i');
    $hasNoteRule = isset($rules['note']) && str_contains($rules['note'], 'max:5000');
    
    if ($hasTimeAmRule && $hasTimePmRule && $hasNoteRule) {
        echo "✓ PASS (Form request validation includes note field)\n";
    } else {
        echo "✗ FAIL (Form request validation incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 4: Check if view files have note field
echo "4. Testing view files: ";
$viewFiles = [
    'resources/views/pages/nurse-notes/create.blade.php',
    'resources/views/pages/nurse-notes/edit.blade.php',
    'resources/views/pages/nurse-notes/show.blade.php',
    'resources/views/pages/nurse-notes/index.blade.php'
];

$allViewsExist = true;
$allHaveNoteField = true;

foreach ($viewFiles as $viewFile) {
    if (!file_exists($viewFile)) {
        $allViewsExist = false;
        break;
    }
    
    $content = file_get_contents($viewFile);
    if (!str_contains($content, 'name="note"') && !str_contains($content, '$note->note')) {
        $allHaveNoteField = false;
        break;
    }
}

if ($allViewsExist && $allHaveNoteField) {
    echo "✓ PASS (All view files exist and have note field)\n";
} else {
    echo "✗ FAIL (View files missing or incorrect)\n";
}

// Test 5: Test creating a nurse note with all fields
echo "5. Testing nurse note creation: ";
try {
    // Get a nurse and morphable record for testing
    $nurse = App\Models\Nurse::first();
    $underReview = App\Models\UnderReview::first();
    
    if ($nurse && $underReview) {
        $nurseNote = App\Models\NurseNote::create([
            'time_am' => '08:00',
            'time_pm' => '20:00',
            'note' => 'Test note content for validation',
            'date' => now()->format('Y-m-d'),
            'morphable_id' => $underReview->id,
            'morphable_type' => 'App\\Models\\UnderReview',
            'nurse_id' => $nurse->id,
        ]);
        
        if ($nurseNote && $nurseNote->note === 'Test note content for validation') {
            echo "✓ PASS (Nurse note created successfully with note field)\n";
            // Clean up
            $nurseNote->delete();
        } else {
            echo "✗ FAIL (Nurse note creation failed)\n";
        }
    } else {
        echo "⚠ SKIP (No nurse or under review records found for testing)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

echo "\nNurse Notes with Note Field Implementation test completed!\n";
