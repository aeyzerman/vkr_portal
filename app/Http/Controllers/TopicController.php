<?php

namespace App\Http\Controllers;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use App\Enums\TopicKind;
use App\Models\Thesis;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Topic::query()
            ->with(['proposedBy', 'reservedFor', 'approvedBy', 'thesis.student'])
            ->withCount([
                'theses as active_assignments_count' => fn($q) => $q
                    ->whereNull('done_at')
                    ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues()),
            ])
            ->latest();

        if ($user->isAdmin()) {
            $topics = $query->paginate(20)->withQueryString();
        } elseif ($user->isSupervisor()) {
            $studentIds = $user->supervisedGroups()
                ->with('students:id,study_group_id')
                ->get()
                ->pluck('students')
                ->flatten()
                ->pluck('id')
                ->all();

            $topics = $query
                ->where(function ($builder) use ($user, $studentIds) {
                    $builder->where('is_approved', true)
                        ->orWhere('proposed_by', $user->id);

                    if ($studentIds !== []) {
                        $builder->orWhereIn('reserved_for', $studentIds);
                    }
                })
                ->paginate(20)
                ->withQueryString();
        } else {
            $topics = $query
                ->where(function ($builder) use ($user) {
                    $builder->where(function ($available) use ($user) {
                        $available->where('is_approved', true)
                            ->where(function ($reserved) use ($user) {
                                $reserved->whereNull('reserved_for')
                                    ->orWhere('reserved_for', $user->id);
                            });
                    })->orWhere('proposed_by', $user->id);
                })
                ->paginate(20)
                ->withQueryString();
        }

        return view('topics.index', [
            'topics' => $topics,
            'user' => $user,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Topic::class);

        $user = $request->user();
        $students = collect();

        if ($user->isSupervisor()) {
            $students = User::query()
                ->whereRaw('(permissions & ?) != 0', [User::PERM_STUDENT])
                ->whereIn('study_group_id', $user->supervisedGroups()->pluck('id'))
                ->orderByRaw('coalesce(full_name, name)')
                ->get(['id', 'name', 'full_name', 'study_group_id']);
        }

        return view('topics.create', [
            'user' => $user,
            'students' => $students,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Topic::class);

        $user = $request->user();

        $rules = [
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
        ];

        if ($user->isSupervisor()) {
            $rules['reserved_for'] = 'nullable|exists:users,id';
        }

        $validated = $request->validate($rules);

        if ($user->isSupervisor() && ! empty($validated['reserved_for'])) {
            $studentAllowed = User::query()
                ->whereKey($validated['reserved_for'])
                ->whereIn('study_group_id', $user->supervisedGroups()->pluck('id'))
                ->exists();

            if (! $studentAllowed) {
                return back()->withErrors([
                    'reserved_for' => 'Можно резервировать тему только за студентом своей группы.',
                ])->withInput();
            }
        }

        $topic = Topic::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'proposed_by' => $user->id,
            'kind' => $user->isStudent() ? TopicKind::StudentProposal : TopicKind::Catalog,
            'reserved_for' => $user->isStudent() ? $user->id : ($validated['reserved_for'] ?? null),
            'is_approved' => $user->isSupervisor() || $user->isAdmin(),
            'approved_by' => ($user->isSupervisor() || $user->isAdmin()) ? $user->id : null,
            'approved_at' => ($user->isSupervisor() || $user->isAdmin()) ? now() : null,
        ]);

        return redirect()->route('topics.show', $topic)
            ->with('success', $user->isStudent() ? 'Тема отправлена на согласование.' : 'Тема добавлена в каталог.');
    }

    public function show(Request $request, Topic $topic)
    {
        $topic->load([
            'proposedBy',
            'reservedFor.studyGroup',
            'approvedBy',
            'theses.student.studyGroup',
            'theses.supervisor',
        ]);

        $user = $request->user();
        $students = collect();

        if ($user->isSupervisor()) {
            $students = User::query()
                ->whereRaw('(permissions & ?) != 0', [User::PERM_STUDENT])
                ->whereIn('study_group_id', $user->supervisedGroups()->pluck('id'))
                ->orderByRaw('coalesce(full_name, name)')
                ->get(['id', 'name', 'full_name', 'study_group_id']);
        }

        return view('topics.show', [
            'topic' => $topic,
            'user' => $user,
            'students' => $students,
        ]);
    }

    public function apply(Request $request, Topic $topic)
    {
        $user = $request->user();

        abort_unless($user->isStudent(), 403);

        if (! $user->study_group_id || ! $user->studyGroup) {
            return back()->with('error', 'Студент должен быть прикреплён к учебной группе.');
        }

        if ($user->activeThesis()->exists()) {
            return back()->with('error', 'У студента уже есть активная работа.');
        }

        if ($user->topicOffers()->exists()) {
            return back()->with('error', 'Сначала примите или отклоните уже предложенную тему.');
        }

        if (! $topic->is_approved) {
            return back()->with('error', 'Тема ещё не согласована.');
        }

        if ($topic->theses()
            ->whereNull('done_at')
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
            ->exists()) {
            return back()->with('error', 'Тема уже занята.');
        }

        if ($topic->reserved_for && $topic->reserved_for !== $user->id) {
            return back()->with('error', 'Тема зарезервирована за другим студентом.');
        }

        Thesis::create([
            'student_id' => $user->id,
            'supervisor_id' => $user->studyGroup->supervisor_id,
            'study_group_id' => $user->study_group_id,
            'topic_id' => $topic->id,
            'assignment_type' => $topic->kind === TopicKind::StudentProposal
                ? ThesisAssignmentType::StudentProposal
                : ThesisAssignmentType::StudentChoice,
            'assignment_status' => ThesisAssignmentStatus::Accepted,
            'assigned_at' => now(),
            'assignment_responded_at' => now(),
            'started_at' => now(),
            'status' => Thesis::STATUS_INITIAL,
        ]);

        $topic->update(['reserved_for' => null]);

        return redirect()->route('thesis.my')
            ->with('success', 'Тема закреплена за студентом.');
    }

    public function assign(Request $request, Topic $topic)
    {
        $user = $request->user();
        abort_unless($user->isSupervisor() || $user->isAdmin(), 403);

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        $student = User::with('studyGroup')->findOrFail($validated['student_id']);

        if (! $student->isStudent()) {
            return back()->with('error', 'Тему можно предлагать только студенту.');
        }

        if (! $student->studyGroup) {
            return back()->with('error', 'Студент не прикреплён к группе.');
        }

        if ($user->isSupervisor() && $student->studyGroup->supervisor_id !== $user->id) {
            abort(403);
        }

        if ($student->activeThesis()->exists()) {
            return back()->with('error', 'У студента уже есть активная работа.');
        }

        if ($student->topicOffers()->exists()) {
            return back()->with('error', 'У студента уже есть необработанное предложение темы.');
        }

        if (! $topic->is_approved) {
            return back()->with('error', 'Нельзя предлагать несогласованную тему.');
        }

        if ($topic->theses()
            ->whereNull('done_at')
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
            ->exists()) {
            return back()->with('error', 'Тема уже закреплена за другим студентом.');
        }

        if ($student->studyGroup->topic_selection_deadline && now()->isAfter($student->studyGroup->topic_selection_deadline)) {
            return back()->with('error', 'Дедлайн выбора темы уже прошёл. Используйте случайное распределение.');
        }

        Thesis::create([
            'student_id' => $student->id,
            'supervisor_id' => $student->studyGroup->supervisor_id,
            'study_group_id' => $student->study_group_id,
            'topic_id' => $topic->id,
            'assignment_type' => ThesisAssignmentType::TeacherOffer,
            'assignment_status' => ThesisAssignmentStatus::Pending,
            'assigned_at' => now(),
            'status' => Thesis::STATUS_INITIAL,
        ]);

        $topic->update(['reserved_for' => $student->id]);

        return redirect()->route('topics.show', $topic)
            ->with('success', 'Предложение темы отправлено студенту.');
    }

    public function approve(Request $request, Topic $topic)
    {
        $this->authorize('approve', Topic::class);

        $topic->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Тема согласована.');
    }

    public function edit(Topic $topic)
    {
        $this->authorize('update', $topic);

        return view('topics.edit', compact('topic'));
    }

    public function update(Request $request, Topic $topic)
    {
        $this->authorize('update', $topic);

        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
        ]);

        $topic->update($validated);

        return redirect()->route('topics.show', $topic)
            ->with('success', 'Тема обновлена.');
    }
}
