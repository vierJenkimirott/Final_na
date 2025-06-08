<?php

namespace App\Http\Controllers;

use App\Models\OffenseCategory;
use App\Models\ViolationType;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class EducatorManualController extends Controller
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
        $categories = OffenseCategory::with(['violationTypes' => function($query) {
            $query->orderByRaw("FIELD(default_penalty, 'W', 'VW', 'WW', 'Pro', 'Exp')");
            $query->orderBy('violation_name');
        }])->get();

        return view('educator.educator-manual', compact('categories'));
    }

    
}