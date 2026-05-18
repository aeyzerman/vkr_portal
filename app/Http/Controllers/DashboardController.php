<?php

namespace App\Http\Controllers;

use App\Models\StudyGroup;
use App\Models\Thesis;
use App\Models\Topic;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isSupervisor()) {
            return $this->supervisorDashboard($user);
        }

        if ($user->isCommission() || $user->isReviewer()) {
            return $this->commissionDashboard();
        }

        // Студент по умолчанию
        return $this->studentDashboard($user);
    }

    private function studentDashboard($user)
    {
        $thesis = $user->activeThesis()->with(['topic', 'supervisor', 'studyGroup'])->first();
        $availableTopics = Topic::available()->with('proposedBy')->latest()->take(10)->get();

        return view('dashboard.student', compact('thesis', 'availableTopics'));
    }

    private function supervisorDashboard($user)
    {
        $theses = $user->supervisedTheses()
            ->active()
            ->with(['student', 'topic', 'studyGroup'])
            ->latest()
            ->get();

        $myTopics = $user->proposedTopics()->withCount('thesis')->latest()->get();

        return view('dashboard.supervisor', compact('theses', 'myTopics'));
    }

    private function commissionDashboard()
    {
        $theses = Thesis::active()
            ->whereIn('status', ['review', 'approved'])
            ->with(['student', 'supervisor', 'topic', 'studyGroup'])
            ->latest()
            ->get();

        return view('dashboard.commission', compact('theses'));
    }

    private function adminDashboard()
    {
        $stats = [
            'total_theses'   => Thesis::active()->count(),
            'total_topics'   => Topic::count(),
            'pending_topics' => Topic::where('is_approved', false)->count(),
            'total_groups'   => StudyGroup::count(),
        ];

        return view('dashboard.admin', compact('stats'));
    }
}
