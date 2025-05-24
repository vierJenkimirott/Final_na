<?php
// Simple script to check violations data directly

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h1>ScholarSync Violations Data Check</h1>";
echo "<p>This script directly checks the violations data in the database.</p>";

// Check total violations
$totalViolations = DB::table('violations')->count();
echo "<h2>Total violations in database: {$totalViolations}</h2>";

// Check active violations
$activeViolations = DB::table('violations')->where('status', 'active')->count();
echo "<h2>Active violations in database: {$activeViolations}</h2>";

// Get first violation details
$firstViolation = DB::table('violations')->first();
echo "<h3>First violation details:</h3>";
echo "<pre>";
print_r($firstViolation);
echo "</pre>";

// Check students with violations
$studentsWithViolations = DB::table('users')
    ->join('violations', 'users.student_id', '=', 'violations.student_id')
    ->select('users.name', 'users.student_id', DB::raw('COUNT(violations.id) as violations_count'))
    ->groupBy('users.id', 'users.name', 'users.student_id')
    ->orderBy('violations_count', 'desc')
    ->get();

echo "<h3>Students with violations:</h3>";
echo "<ul>";
foreach ($studentsWithViolations as $student) {
    echo "<li>{$student->name} ({$student->student_id}): {$student->violations_count} violations</li>";
}
echo "</ul>";

// Fix for the dashboard - direct update of the violations count in the database
echo "<h3>Fixing dashboard issues:</h3>";

// Check if we need to update the cache
$cacheKey = 'dashboard_violations_count';
if (DB::table('cache')->where('key', 'LIKE', '%' . $cacheKey . '%')->count() > 0) {
    echo "<p>Clearing cache for dashboard violations count...</p>";
    DB::table('cache')->where('key', 'LIKE', '%' . $cacheKey . '%')->delete();
    echo "<p>Cache cleared successfully.</p>";
} else {
    echo "<p>No cache entries found for dashboard violations count.</p>";
}

echo "<p>Dashboard should now display the correct violations count: {$activeViolations}</p>";
echo "<p><a href='/educator/dashboard'>Go to Dashboard</a></p>";
