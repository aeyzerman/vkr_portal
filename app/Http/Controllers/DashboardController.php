<?php

namespace App\Http\Controllers;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisStatus;
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

        return $this->studentDashboard($user);
    }

    private function studentDashboard($user)
    {
        $thesis = $user->activeThesis()->with(['topic', 'supervisor', 'studyGroup'])->first();
        $pendingOffers = $user->topicOffers()->with(['topic', 'supervisor'])->latest('assigned_at')->get();

        $availableTopics = Topic::available()
            ->where(function ($query) use ($user) {
                $query->whereNull('reserved_for')
                    ->orWhere('reserved_for', $user->id);
            })
            ->with('proposedBy')
            ->latest()
            ->take(8)
            ->get();

        $myTopics = $user->proposedTopics()->latest()->take(5)->get();

        return view('dashboard.student', compact('thesis', 'pendingOffers', 'availableTopics', 'myTopics'));
    }

    private function supervisorDashboard($user)
    {
        $groups = $user->supervisedGroups()
            ->withCount('students')
            ->with(['students', 'activeTheses'])
            ->orderBy('name')
            ->get();

        $pendingOffers = $user->supervisedTheses()
            ->whereNull('done_at')
            ->where('assignment_status', ThesisAssignmentStatus::Pending->value)
            ->with(['student.studyGroup', 'topic'])
            ->latest('assigned_at')
            ->get();

        $theses = $user->supervisedTheses()
            ->whereNull('done_at')
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
            ->with(['student', 'topic', 'studyGroup'])
            ->latest()
            ->take(10)
            ->get();

        $myTopics = $user->proposedTopics()->withCount('theses')->latest()->take(10)->get();

        return view('dashboard.supervisor', compact('groups', 'pendingOffers', 'theses', 'myTopics'));
    }

    private function commissionDashboard()
    {
        $theses = Thesis::query()
            ->whereNull('done_at')
            ->whereIn('status', [ThesisStatus::Review->value, ThesisStatus::Approved->value])
            ->with(['student', 'supervisor', 'topic', 'studyGroup'])
            ->latest()
            ->get();

        return view('dashboard.commission', compact('theses'));
    }

    private function adminDashboard()
    {
        $stats = [
            'total_theses' => Thesis::whereNull('done_at')->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())->count(),
            'pending_offers' => Thesis::whereNull('done_at')->where('assignment_status', ThesisAssignmentStatus::Pending->value)->count(),
            'total_topics' => Topic::count(),
            'pending_topics' => Topic::where('is_approved', false)->count(),
            'total_groups' => StudyGroup::count(),
        ];

        $recentGroups = StudyGroup::with(['supervisor'])->latest()->take(5)->get();
        $recentTopics = Topic::with(['proposedBy'])->latest()->take(5)->get();

        return view('dashboard.admin', compact('stats', 'recentGroups', 'recentTopics'));
    }
}
