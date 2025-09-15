<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Nurse Notes Time Fields Implementation...\n\n";

// Test 1: Check if table structure is correct
echo "1. Testing database table structure: ";
try {
    $columns = DB::select("SHOW COLUMNS FROM nurse_notes");
    $hasTimeAm = false;
    $hasTimePm = false;
    $hasNoteAm = false;
    $hasNotePm = false;
    
    foreach ($columns as $column) {
        if ($column->Field === 'time_am' && $column->Type === 'time') {
            $hasTimeAm = true;
        }
        if ($column->Field === 'time_pm' && $column->Type === 'time') {
            $hasTimePm = true;
        }
        if ($column->Field === 'note_am') {
            $hasNoteAm = true;
        }
        if ($column->Field === 'note_pm') {
            $hasNotePm = true;
        }
    }
    
    if ($hasTimeAm && $hasTimePm && !$hasNoteAm && !$hasNotePm) {
        echo "✓ PASS (Time fields exist, old text fields removed)\n";
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
    $hasNoteAm = in_array('note_am', $fillable);
    $hasNotePm = in_array('note_pm', $fillable);
    
    if ($hasTimeAm && $hasTimePm && !$hasNoteAm && !$hasNotePm) {
        echo "✓ PASS (Model fillable fields updated)\n";
    } else {
        echo "✗ FAIL (Model fillable fields incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 3: Check if model casts are correct
echo "3. Testing model casts: ";
try {
    $note = new App\Models\NurseNote();
    $casts = $note->getCasts();
    
    $hasTimeAmCast = isset($casts['time_am']) && $casts['time_am'] === 'datetime:H:i';
    $hasTimePmCast = isset($casts['time_pm']) && $casts['time_pm'] === 'datetime:H:i';
    
    if ($hasTimeAmCast && $hasTimePmCast) {
        echo "✓ PASS (Model casts are correct)\n";
    } else {
        echo "✗ FAIL (Model casts incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 4: Check if form request validation is updated
echo "4. Testing form request validation: ";
try {
    $storeRequest = new App\Http\Requests\StoreNurseNoteRequest();
    $rules = $storeRequest->rules();
    
    $hasTimeAmRule = isset($rules['time_am']) && str_contains($rules['time_am'], 'date_format:H:i');
    $hasTimePmRule = isset($rules['time_pm']) && str_contains($rules['time_pm'], 'date_format:H:i');
    $hasNoteAmRule = isset($rules['note_am']);
    $hasNotePmRule = isset($rules['note_pm']);
    
    if ($hasTimeAmRule && $hasTimePmRule && !$hasNoteAmRule && !$hasNotePmRule) {
        echo "✓ PASS (Form request validation updated)\n";
    } else {
        echo "✗ FAIL (Form request validation incorrect)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 5: Check if controller methods are updated
echo "5. Testing controller methods: ";
try {
    $controller = new App\Http\Controllers\NurseNoteController();
    $reflection = new ReflectionClass($controller);
    
    // Check if store method exists and has nurse validation
    $storeMethod = $reflection->getMethod('store');
    $storeSource = file_get_contents($reflection->getFileName());
    $storeStart = $storeMethod->getStartLine();
    $storeEnd = $storeMethod->getEndLine();
    $storeCode = implode('', array_slice(explode("\n", $storeSource), $storeStart - 1, $storeEnd - $storeStart + 1));
    
    $hasNurseValidation = str_contains($storeCode, 'nurse->id') && str_contains($storeCode, 'nurse profile');
    
    if ($hasNurseValidation) {
        echo "✓ PASS (Controller has nurse validation)\n";
    } else {
        echo "✗ FAIL (Controller missing nurse validation)\n";
    }
} catch (Exception $e) {
    echo "✗ FAIL - " . $e->getMessage() . "\n";
}

// Test 6: Check if view files exist and have time fields
echo "6. Testing view files: ";
$viewFiles = [
    'resources/views/pages/nurse-notes/create.blade.php',
    'resources/views/pages/nurse-notes/edit.blade.php',
    'resources/views/pages/nurse-notes/show.blade.php',
    'resources/views/pages/nurse-notes/index.blade.php'
];

$allViewsExist = true;
$allHaveTimeFields = true;

foreach ($viewFiles as $viewFile) {
    if (!file_exists($viewFile)) {
        $allViewsExist = false;
        break;
    }
    
    $content = file_get_contents($viewFile);
    if (!str_contains($content, 'time_am') || !str_contains($content, 'time_pm')) {
        $allHaveTimeFields = false;
        break;
    }
}

if ($allViewsExist && $allHaveTimeFields) {
    echo "✓ PASS (All view files exist and have time fields)\n";
} else {
    echo "✗ FAIL (View files missing or incorrect)\n";
}

echo "\nNurse Notes Time Fields Implementation test completed!\n";
