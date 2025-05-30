<!-- ?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\EducatorController;

echo "===== TESTING BEHAVIOR CHART FUNCTIONALITY =====\n\n";

// 1. Check if we have violations with sex
echo "CHECKING VIOLATIONS WITH SEX:\n";
$violations = DB::table('violations')->get();
$count = $violations->count();

echo "Total violations: {$count}\n";

// Count by sex using the same logic as the behavior chart
$maleCount = DB::table('violations')->where(function($query) {
    $query->where('sex', 'male')
          ->orWhere('sex', 'Male')
          ->orWhere('sex', 'M')
          ->orWhere('sex', 'boy')
          ->orWhere('sex', 'Boy')
          ->orWhere('sex', 'man')
          ->orWhere('sex', 'men');
})->count();

$femaleCount = DB::table('violations')->where(function($query) {
    $query->where('sex', 'female')
          ->orWhere('sex', 'Female')
          ->orWhere('sex', 'F')
          ->orWhere('sex', 'girl')
          ->orWhere('sex', 'Girl')
          ->orWhere('sex', 'woman')
          ->orWhere('sex', 'women');
})->count();

echo "Male violations: {$maleCount}\n";
echo "Female violations: {$femaleCount}\n";

// 2. List all violations with their details
echo "\nLISTING ALL VIOLATIONS:\n";
foreach ($violations as $v) {
    echo "ID: {$v->id}, Student ID: {$v->student_id}, Sex: {$v->sex}, ";
    echo "Date: {$v->violation_date}, Severity: {$v->severity}\n";
}

// 3. Test the behavior chart functionality
echo "\nTESTING BEHAVIOR CHART FUNCTIONALITY:\n";

// Create an instance of the EducatorController
$controller = new EducatorController();

// Call the getBehaviorDataBySex method
$result = $controller->getBehaviorDataBySex(12); // Get 12 months of data

// Output the result
echo "\nBEHAVIOR CHART DATA:\n";
echo "Labels: " . implode(", ", $result['labels']) . "\n";
echo "Boys Data: " . implode(", ", $result['boys']) . "\n";
echo "Girls Data: " . implode(", ", $result['girls']) . "\n";

// 4. Check specifically for April data
echo "\nCHECKING APRIL DATA:\n";
$aprilIndex = array_search('April', $result['labels']);

if ($aprilIndex !== false) {
    echo "April Boys Score: {$result['boys'][$aprilIndex]}\n";
    echo "April Girls Score: {$result['girls'][$aprilIndex]}\n";
    
    // Verify if the scores reflect violations
    echo "\nVERIFYING APRIL SCORES:\n";
    
    // Check for April violations
    $aprilViolations = DB::table('violations')
        ->whereRaw("violation_date LIKE ?", ['%April%'])
        ->orWhereRaw("MONTH(violation_date) = ?", [4])
        ->get();
        
    echo "April violations found: {$aprilViolations->count()}\n";
    
    foreach ($aprilViolations as $v) {
        echo "  - ID: {$v->id}, Gender: {$v->gender}, Severity: {$v->severity}\n";
    }
} else {
    echo "April not found in labels\n";
}

echo "\n===== TEST COMPLETED =====\n";

// 3. Now let's simulate the behavior chart calculation
echo "\nTESTING BEHAVIOR CHART CALCULATION:\n";

// Get the current month and year
$currentDate = Carbon::now();
$monthNum = 4; // April
$yearNum = 2025;
$monthName = 'April';

// Query boys violations for April
$boysViolations = DB::table('violations')
    ->where(function($query) {
        $query->where('gender', 'male')
              ->orWhere('gender', 'Male')
              ->orWhere('gender', 'M')
              ->orWhere('gender', 'boy')
              ->orWhere('gender', 'Boy');
    })
    ->where(function($query) use ($monthNum, $yearNum, $monthName) {
        $query->where(function($q) use ($monthNum, $yearNum) {
            $q->whereRaw("MONTH(violation_date) = ?", [$monthNum])
              ->whereRaw("YEAR(violation_date) = ?", [$yearNum]);
        })
        ->orWhere(function($q) use ($monthName, $yearNum) {
            $q->whereRaw("violation_date LIKE ?", ["%{$monthName} {$yearNum}%"]);
        })
        ->orWhere(function($q) use ($monthName) {
            $q->whereRaw("violation_date LIKE ?", ["%{$monthName}%"]);
        });
    })
    ->where('status', 'active')
    ->get();

echo "Boys violations found for April: " . $boysViolations->count() . "\n";

// Query girls violations for April
$girlsViolations = DB::table('violations')
    ->where(function($query) {
        $query->where('gender', 'female')
              ->orWhere('gender', 'Female')
              ->orWhere('gender', 'F')
              ->orWhere('gender', 'girl')
              ->orWhere('gender', 'Girl');
    })
    ->where(function($query) use ($monthNum, $yearNum, $monthName) {
        $query->where(function($q) use ($monthNum, $yearNum) {
            $q->whereRaw("MONTH(violation_date) = ?", [$monthNum])
              ->whereRaw("YEAR(violation_date) = ?", [$yearNum]);
        })
        ->orWhere(function($q) use ($monthName, $yearNum) {
            $q->whereRaw("violation_date LIKE ?", ["%{$monthName} {$yearNum}%"]);
        })
        ->orWhere(function($q) use ($monthName) {
            $q->whereRaw("violation_date LIKE ?", ["%{$monthName}%"]);
        });
    })
    ->where('status', 'active')
    ->get();

echo "Girls violations found for April: " . $girlsViolations->count() . "\n";

// Calculate behavior scores
$boysScore = 100;
$girlsScore = 100;

// Process boys violations
foreach ($boysViolations as $violation) {
    $severityName = $violation->severity ?? '';
    $severity = strtolower($severityName);
    
    echo "Processing boy violation with severity: {$severityName}\n";
    
    if (strpos($severity, 'low') !== false) {
        $boysScore -= 5; // -5 for Low severity
        echo "Applied -5 deduction for Low severity\n";
    } elseif (strpos($severity, 'medium') !== false) {
        $boysScore -= 10; // -10 for Medium severity
        echo "Applied -10 deduction for Medium severity\n";
    } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
        $boysScore -= 15; // -15 for High severity
        echo "Applied -15 deduction for High severity\n";
    } elseif (strpos($severity, 'very high') !== false) {
        $boysScore -= 20; // -20 for Very High severity
        echo "Applied -20 deduction for Very High severity\n";
    } else {
        // If severity is empty or unrecognized, apply a default deduction based on the violation
        if ($violation->penalty == 'VW') {
            $boysScore -= 10; // -10 for Verbal Warning
            echo "Applied -10 deduction for Verbal Warning penalty\n";
        } elseif ($violation->penalty == 'WW') {
            $boysScore -= 15; // -15 for Written Warning
            echo "Applied -15 deduction for Written Warning penalty\n";
        } elseif ($violation->penalty == 'Pro' || $violation->penalty == 'Exp') {
            $boysScore -= 20; // -20 for Probation or Expulsion
            echo "Applied -20 deduction for {$violation->penalty} penalty\n";
        } else {
            $boysScore -= 10; // Default deduction
            echo "Applied -10 default deduction\n";
        }
    }
}

// Process girls violations
foreach ($girlsViolations as $violation) {
    $severityName = $violation->severity ?? '';
    $severity = strtolower($severityName);
    
    echo "Processing girl violation with severity: {$severityName}\n";
    
    if (strpos($severity, 'low') !== false) {
        $girlsScore -= 5; // -5 for Low severity
        echo "Applied -5 deduction for Low severity\n";
    } elseif (strpos($severity, 'medium') !== false) {
        $girlsScore -= 10; // -10 for Medium severity
        echo "Applied -10 deduction for Medium severity\n";
    } elseif (strpos($severity, 'high') !== false && strpos($severity, 'very') === false) {
        $girlsScore -= 15; // -15 for High severity
        echo "Applied -15 deduction for High severity\n";
    } elseif (strpos($severity, 'very high') !== false) {
        $girlsScore -= 20; // -20 for Very High severity
        echo "Applied -20 deduction for Very High severity\n";
    } else {
        // If severity is empty or unrecognized, apply a default deduction based on the violation
        if ($violation->penalty == 'VW') {
            $girlsScore -= 10; // -10 for Verbal Warning
            echo "Applied -10 deduction for Verbal Warning penalty\n";
        } elseif ($violation->penalty == 'WW') {
            $girlsScore -= 15; // -15 for Written Warning
            echo "Applied -15 deduction for Written Warning penalty\n";
        } elseif ($violation->penalty == 'Pro' || $violation->penalty == 'Exp') {
            $girlsScore -= 20; // -20 for Probation or Expulsion
            echo "Applied -20 deduction for {$violation->penalty} penalty\n";
        } else {
            $girlsScore -= 10; // Default deduction
            echo "Applied -10 default deduction\n";
        }
    }
}

// Ensure scores are within 0 to 100 range
$boysScore = max(0, min(100, $boysScore));
$girlsScore = max(0, min(100, $girlsScore));

echo "\nFINAL BEHAVIOR SCORES FOR APRIL:\n";
echo "Boys score: {$boysScore}\n";
echo "Girls score: {$girlsScore}\n";

echo "\n===== TEST COMPLETED =====\n"; -->
