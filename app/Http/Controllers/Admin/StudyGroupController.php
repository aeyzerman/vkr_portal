<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use App\Enums\TopicKind;
use App\Http\Controllers\Controller;
use App\Models\StudyGroup;
use App\Models\Thesis;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class StudyGroupController extends Controller
{
    public function index()
    {
        $groups = StudyGroup::with(['supervisor'])
            ->withCount(['students', 'theses'])
            ->orderBy('enrollment_year', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.groups.index', compact('groups'));
    }

    public function create()
    {
        $supervisors = User::query()
            ->whereRaw('(permissions & ?) != 0', [User::PERM_SUPERVISOR])
            ->orderByRaw('coalesce(full_name, name)')
            ->get(['id', 'full_name', 'name']);

        return view('admin.groups.create', compact('supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'course' => 'required|integer|min:1|max:6',
            'specialty_code' => 'required|string|max:20',
            'specialty_name' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:users,id|unique:study_groups,supervisor_id',
            'enrollment_year' => 'required|integer|min:2000|max:2099',
            'topic_selection_deadline' => 'nullable|date',
        ]);

        $group = StudyGroup::create($validated);

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Группа создана.');
    }

    public function show(StudyGroup $studyGroup)
    {
        $user = request()->user();

        if (! $user->isAdmin() && $studyGroup->supervisor_id !== $user->id) {
            abort(403);
        }

        $studyGroup->load(['supervisor', 'students']);

        $theses = $studyGroup->theses()
            ->with(['student', 'topic', 'supervisor'])
            ->latest()
            ->get();

        $availableTopics = Topic::available()
            ->where('kind', TopicKind::Catalog->value)
            ->with(['proposedBy', 'reservedFor'])
            ->get();

        return view('admin.groups.show', compact('studyGroup', 'theses', 'availableTopics'));
    }

    public function edit(StudyGroup $studyGroup)
    {
        $supervisors = User::query()
            ->whereRaw('(permissions & ?) != 0', [User::PERM_SUPERVISOR])
            ->where(function ($query) use ($studyGroup) {
                $query->whereDoesntHave('supervisedGroups')
                    ->orWhere('id', $studyGroup->supervisor_id);
            })
            ->orderByRaw('coalesce(full_name, name)')
            ->get(['id', 'full_name', 'name']);

        return view('admin.groups.edit', compact('studyGroup', 'supervisors'));
    }

    public function update(Request $request, StudyGroup $studyGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'course' => 'required|integer|min:1|max:6',
            'specialty_code' => 'required|string|max:20',
            'specialty_name' => 'required|string|max:255',
            'supervisor_id' => 'required|exists:users,id|unique:study_groups,supervisor_id,' . $studyGroup->id,
            'enrollment_year' => 'required|integer|min:2000|max:2099',
            'topic_selection_deadline' => 'nullable|date',
        ]);

        $studyGroup->update($validated);

        return back()->with('success', 'Группа обновлена.');
    }

    public function randomAssign(Request $request, StudyGroup $studyGroup)
    {
        $user = $request->user();

        if (! $user->isAdmin() && $studyGroup->supervisor_id !== $user->id) {
            abort(403);
        }

        if (! $studyGroup->topic_selection_deadline || now()->isBefore($studyGroup->topic_selection_deadline)) {
            return back()->with('error', 'Случайное распределение доступно только после дедлайна выбора темы.');
        }

        $students = $studyGroup->students()
            ->whereRaw('(permissions & ?) != 0', [User::PERM_STUDENT])
            ->get();

        $studentsWithoutThesis = $students->filter(fn(User $student) => ! $student->activeThesis()->exists())->shuffle()->values();

        $topics = Topic::available()
            ->where('kind', TopicKind::Catalog->value)
            ->get()
            ->filter(function (Topic $topic) use ($studentsWithoutThesis) {
                if ($topic->reserved_for === null) {
                    return true;
                }

                return $studentsWithoutThesis->contains('id', $topic->reserved_for);
            })
            ->shuffle()
            ->values();

        $assigned = 0;

        foreach ($studentsWithoutThesis as $student) {
            $topic = $topics->first(function (Topic $availableTopic) use ($student) {
                return $availableTopic->reserved_for === null || $availableTopic->reserved_for === $student->id;
            });

            if (! $topic) {
                continue;
            }

            Thesis::query()
                ->where('student_id', $student->id)
                ->whereNull('done_at')
                ->where('assignment_status', ThesisAssignmentStatus::Pending->value)
                ->update([
                    'assignment_status' => ThesisAssignmentStatus::Declined->value,
                    'assignment_responded_at' => now(),
                    'done_at' => now(),
                ]);

            Thesis::create([
                'student_id' => $student->id,
                'supervisor_id' => $studyGroup->supervisor_id,
                'study_group_id' => $studyGroup->id,
                'topic_id' => $topic->id,
                'assignment_type' => ThesisAssignmentType::RandomAssignment,
                'assignment_status' => ThesisAssignmentStatus::Assigned,
                'assigned_at' => now(),
                'assignment_responded_at' => now(),
                'started_at' => now(),
                'status' => Thesis::STATUS_INITIAL,
            ]);

            $topic->update(['reserved_for' => null]);
            $topics = $topics->reject(fn(Topic $candidate) => $candidate->id === $topic->id)->values();
            $assigned++;
        }

        return back()->with(
            $assigned > 0 ? 'success' : 'error',
            $assigned > 0
                ? "Случайно распределено тем: {$assigned}."
                : 'Не удалось распределить темы: не хватает доступных тем.'
        );
    }
}
