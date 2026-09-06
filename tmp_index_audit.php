<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$database = DB::getDatabaseName();
echo "database={$database}\n";

$tables = collect(DB::select('SHOW TABLES'))
    ->map(fn ($row) => array_values((array) $row)[0])
    ->filter(fn ($name) => ! str_starts_with($name, 'wirechat_') || true)
    ->values()
    ->all();

echo 'table_count='.count($tables)."\n";

$pkOnly = [];
$missing = [];

foreach ($tables as $table) {
    try {
        $indexes = DB::select("SHOW INDEX FROM `{$table}`");
    } catch (Throwable $e) {
        echo "skip {$table}: {$e->getMessage()}\n";
        continue;
    }

    $indexedCols = [];
    $indexNames = [];
    foreach ($indexes as $idx) {
        $indexedCols[$idx->Column_name] = true;
        $indexNames[$idx->Key_name] = true;
    }

    $cols = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))->pluck('Field')->all();

    $candidates = array_values(array_filter($cols, function ($c) {
        return str_ends_with($c, '_id')
            || in_array($c, [
                'status', 'is_completed', 'is_discharged', 'is_occupied',
                'is_active', 'deleted_at', 'email', 'phone', 'nid', 'date',
                'priority', 'ref_no',
            ], true);
    }));

    $unindexed = [];
    foreach ($candidates as $c) {
        if (! isset($indexedCols[$c])) {
            $unindexed[] = $c;
        }
    }

    $secondary = array_filter(array_keys($indexNames), fn ($n) => $n !== 'PRIMARY');
    if (count($secondary) === 0) {
        $pkOnly[] = $table;
    }

    if ($unindexed) {
        $missing[$table] = $unindexed;
    }
}

echo "pk_only_count=".count($pkOnly)."\n";
foreach ($pkOnly as $t) {
    echo "PK_ONLY {$t}\n";
}

echo "---MISSING_CANDIDATES---\n";
foreach ($missing as $table => $cols) {
    echo "{$table}: ".implode(', ', $cols)."\n";
}
