<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\User;
use Illuminate\Http\Request;

class StudyGroupController extends Controller
{
    public function index()
    {
        $groups = StudyGroup::with(['supervisor'])
            ->withCount(['theses', 'activeTheses'])
            ->orderBy('enrollment_year', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        $supervisors = User::where('permissions', '&', User::PERM_SUPERVISOR)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'name']);

        return view('admin.groups.create', compact('supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:50',
            'course'           => 'required|integer|min:1|max:6',
            'specialty_code'   => 'required|string|max:20',
            'specialty_name'   => 'required|string|max:255',
            'supervisor_id'    => 'required|exists:users,id',
            'enrollment_year'  => 'required|integer|min:2000|max:2099',
        ]);

        $group = StudyGroup::create($validated);

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Группа создана.');
    }

    public function show(StudyGroup $studyGroup)
    {
        $studyGroup->load(['supervisor']);

        $theses = $studyGroup->theses()
            ->with(['student', 'topic', 'supervisor'])
            ->active()
            ->orderBy('status')
            ->get();

        return view('admin.groups.show', compact('studyGroup', 'theses'));
    }

    public function edit(StudyGroup $studyGroup)
    {
        $supervisors = User::where('permissions', '&', User::PERM_SUPERVISOR)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'name']);

        return view('admin.groups.edit', compact('studyGroup', 'supervisors'));
    }

    public function update(Request $request, StudyGroup $studyGroup)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:50',
            'course'          => 'required|integer|min:1|max:6',
            'specialty_code'  => 'required|string|max:20',
            'specialty_name'  => 'required|string|max:255',
            'supervisor_id'   => 'required|exists:users,id',
            'enrollment_year' => 'required|integer|min:2000|max:2099',
        ]);

        $studyGroup->update($validated);

        return back()->with('success', 'Группа обновлена.');
    }
}
