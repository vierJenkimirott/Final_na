<?php

// This is a simple test script to check if the violations are being properly retrieved
// and formatted for the behavior chart

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Initialize Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get current year
$currentYear = date('Y');
$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$shortMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

echo "Testing Violation Data Retrieval for Behavior Chart\n";
echo "==================================================\n\n";

// Count total violations
$totalViolations = \App\Models\Violation::where('status', 'active')->count();
echo "Total violations in database: $totalViolations\n\n";

// Count violations by gender
$maleViolations = \App\Models\Violation::where(function($query) {
    $query->where('sex', 'male')
          ->orWhere('sex', 'Male');
})->where('status', 'active')->count();

$femaleViolations = \App\Models\Violation::where(function($query) {
    $query->where('sex', 'female')
          ->orWhere('sex', 'Female');
})->where('status', 'active')->count();

echo "Male violations: $maleViolations\n";
echo "Female violations: $femaleViolations\n\n";

// Count violations by month for males
echo "Male violations by month:\n";
$maleViolationsByMonth = [];

foreach ($months as $index => $month) {
    $monthNum = $index + 1;
    
    // Query using violation_date
    $count = \App\Models\Violation::where(function($query) {
        $query->where('sex', 'male')
              ->orWhere('sex', 'Male');
    })
    ->whereMonth('violation_date', $monthNum)
    ->whereYear('violation_date', $currentYear)
    ->where('status', 'active')
    ->count();
    
    $maleViolationsByMonth[$shortMonths[$index]] = $count;
    echo "  - $month: $count\n";
}

// Count violations by month for females
echo "\nFemale violations by month:\n";
$femaleViolationsByMonth = [];

foreach ($months as $index => $month) {
    $monthNum = $index + 1;
    
    // Query using violation_date
    $count = \App\Models\Violation::where(function($query) {
        $query->where('sex', 'female')
              ->orWhere('sex', 'Female');
    })
    ->whereMonth('violation_date', $monthNum)
    ->whereYear('violation_date', $currentYear)
    ->where('status', 'active')
    ->count();
    
    $femaleViolationsByMonth[$shortMonths[$index]] = $count;
    echo "  - $month: $count\n";
}

// Format data for JavaScript
echo "\nJavaScript data format:\n";
echo "window.maleViolationsByMonth = " . json_encode($maleViolationsByMonth) . ";\n";
echo "window.femaleViolationsByMonth = " . json_encode($femaleViolationsByMonth) . ";\n";

// Check a few individual violations to understand their structure
echo "\nSample violations:\n";
$sampleViolations = \App\Models\Violation::where('status', 'active')->take(3)->get();

foreach ($sampleViolations as $index => $violation) {
    echo "Violation " . ($index + 1) . ":\n";
    echo "  - ID: {$violation->id}\n";
    echo "  - Student ID: {$violation->student_id}\n";
    echo "  - Date: {$violation->violation_date}\n";
    echo "  - Sex: {$violation->sex}\n";
    echo "  - Status: {$violation->status}\n";
    echo "  - Created at: {$violation->created_at}\n\n";
}

echo "==================================================\n";
echo "Test completed. Use this information to debug the behavior chart.\n";
