<?php

namespace App\Http\Controllers;

use App\Models\OffenseCategory;
use App\Models\ViolationType;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class StudentManualController extends Controller
{
    protected $unreadCount = 0;

    public function __construct()
    {
        try {
            if (Auth::check()) {
                $this->unreadCount = Notification::where('user_id', Auth::id())
                    ->where('is_read', false)
                    ->count();
            }
        } catch (\Exception $e) {
            $this->unreadCount = 0;
        }
        
        View::share('unreadCount', $this->unreadCount);
    }

    public function index()
    {
        $user = Auth::user();

        // Update last_manual_viewed_at for the current user
        if ($user) {
            $user->update(['last_manual_viewed_at' => Carbon::now()]);
        }

        // Get all offense categories with their violation types, sorted by severity and then alphabetically
        $categories = OffenseCategory::with(['violationTypes' => function($query) {
            $query->orderByRaw("FIELD(default_penalty, 'W', 'VW', 'WW', 'Pro', 'Exp')");
            $query->orderBy('violation_name');
        }])->get();

        // Determine if manual is new for the sidebar
        $isManualNew = false;
        if ($user) {
            $lastOffenseCategoryUpdate = OffenseCategory::max('updated_at');
            $lastViolationTypeUpdate = ViolationType::max('updated_at');

            $manualLastUpdated = null;
            if ($lastOffenseCategoryUpdate && $lastViolationTypeUpdate) {
                $manualLastUpdated = max($lastOffenseCategoryUpdate, $lastViolationTypeUpdate);
            } elseif ($lastOffenseCategoryUpdate) {
                $manualLastUpdated = $lastOffenseCategoryUpdate;
            } elseif ($lastViolationTypeUpdate) {
                $manualLastUpdated = $lastViolationTypeUpdate;
            }

            if ($manualLastUpdated && $user->last_manual_viewed_at) {
                $isManualNew = Carbon::parse($manualLastUpdated)->greaterThan(Carbon::parse($user->last_manual_viewed_at));
            } elseif ($manualLastUpdated) {
                // If user has never viewed, but manual has updates, consider it new
                $isManualNew = true;
            }
        }
        
        View::share('isManualNew', $isManualNew);

        return view('student.student-manual', compact('categories'));
    }

}



