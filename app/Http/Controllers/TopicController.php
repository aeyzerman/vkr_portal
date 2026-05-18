<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    // Список тем (для студента - свободные, для руководителя - свои, для админа - все)
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $topics = Topic::with(['proposedBy', 'reservedFor'])->latest()->paginate(20);
        } elseif ($user->isSupervisor()) {
            $topics = Topic::where('proposed_by', $user->id)
                ->with(['reservedFor'])
                ->withCount('thesis')
                ->latest()
                ->paginate(20);
        } else {
            // Студент видит одобренные свободные темы
            $topics = Topic::available()->with('proposedBy')->latest()->paginate(20);
        }

        return view('topics.index', compact('topics'));
    }

    public function create()
    {
        // Предложить тему может студент или руководитель
        $this->authorize('create', Topic::class);

        $students = [];
        if (auth()->user()->isSupervisor()) {
            // Руководитель может сразу зарезервировать тему за студентом
            $students = User::where('permissions', '&', User::PERM_STUDENT)
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'name']);
        }

        return view('topics.create', compact('students'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Topic::class);

        $validated = $request->validate([
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'reserved_for' => 'nullable|exists:users,id',
        ]);

        $topic = Topic::create([
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'proposed_by'  => $request->user()->id,
            'reserved_for' => $validated['reserved_for'] ?? null,
            // Если предлагает руководитель - сразу одобрено, студент - нет
            'is_approved'  => $request->user()->isSupervisor(),
        ]);

        return redirect()->route('topics.show', $topic)
            ->with('success', 'Тема успешно предложена.');
    }

    public function show(Topic $topic)
    {
        $topic->load(['proposedBy', 'reservedFor', 'thesis.student']);
        return view('topics.show', compact('topic'));
    }

    // Студент подаёт заявку на тему - создаёт thesis c этой темой
    public function apply(Request $request, Topic $topic)
    {
        $user = $request->user();

        if (! $user->isStudent()) {
            abort(403);
        }

        // Тема должна быть одобрена и свободна
        if (! $topic->is_approved) {
            return back()->with('error', 'Тема ещё не одобрена.');
        }

        if ($topic->thesis()->whereNull('done_at')->exists()) {
            return back()->with('error', 'Тема уже занята.');
        }

        // Тема зарезервирована за другим студентом
        if ($topic->reserved_for && $topic->reserved_for !== $user->id) {
            return back()->with('error', 'Тема зарезервирована за другим студентом.');
        }

        // У студента уже есть активная работа
        if ($user->activeThesis) {
            return back()->with('error', 'У вас уже есть активная работа. Завершите её перед выбором новой темы.');
        }

        // Студент должен состоять в группе
        $thesis = $user->theses()->whereNull('done_at')->first();
        $groupThesis = Thesis::where('student_id', $user->id)->whereNull('done_at')->first();

        // Создаём работу - руководитель берётся из темы или нужно выбрать
        $validated = $request->validate([
            'supervisor_id'  => 'required|exists:users,id',
            'study_group_id' => 'required|exists:study_groups,id',
        ]);

        Thesis::create([
            'student_id'     => $user->id,
            'supervisor_id'  => $validated['supervisor_id'],
            'study_group_id' => $validated['study_group_id'],
            'topic_id'       => $topic->id,
            'status'         => 'draft',
        ]);

        return redirect()->route('thesis.my')
            ->with('success', 'Заявка на тему подана. Ожидайте подтверждения руководителя.');
    }

    // Руководитель назначает тему студенту напрямую
    public function assign(Request $request, Topic $topic)
    {
        $user = $request->user();

        if (! $user->isSupervisor()) {
            abort(403);
        }

        $validated = $request->validate([
            'student_id'     => 'required|exists:users,id',
            'study_group_id' => 'required|exists:study_groups,id',
        ]);

        $student = User::findOrFail($validated['student_id']);

        if ($student->activeThesis) {
            return back()->with('error', 'У студента уже есть активная работа.');
        }

        Thesis::create([
            'student_id'     => $validated['student_id'],
            'supervisor_id'  => $user->id,
            'study_group_id' => $validated['study_group_id'],
            'topic_id'       => $topic->id,
            'status'         => 'draft',
        ]);

        // Снимаем резервирование если было
        $topic->update(['reserved_for' => null]);

        return redirect()->route('topics.show', $topic)
            ->with('success', 'Тема назначена студенту.');
    }

    // Админ одобряет тему
    public function approve(Topic $topic)
    {
        $this->authorize('approve', Topic::class);

        $topic->update(['is_approved' => true]);

        return back()->with('success', 'Тема одобрена.');
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
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
        ]);

        $topic->update($validated);

        return redirect()->route('topics.show', $topic)
            ->with('success', 'Тема обновлена.');
    }
}
