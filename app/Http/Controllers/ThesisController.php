<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThesisController extends Controller
{
    // Страница своей активной работы (для студента)
    public function my(Request $request)
    {
        $user = $request->user();

        $thesis = $user->activeThesis()
            ->with(['topic', 'supervisor', 'studyGroup'])
            ->first();

        // История завершённых работ
        $history = $user->theses()
            ->completed()
            ->with(['topic', 'studyGroup'])
            ->latest('done_at')
            ->get();

        return view('thesis.my', compact('thesis', 'history'));
    }

    public function show(Thesis $thesis)
    {
        $this->authorize('view', $thesis);

        $thesis->load(['student', 'supervisor', 'topic', 'studyGroup']);

        return view('thesis.show', compact('thesis'));
    }

    // Студент загружает документ
    public function uploadDocument(Request $request, Thesis $thesis)
    {
        $this->authorize('uploadDocument', $thesis);

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:20480', // макс 20MB
        ]);

        // Удаляем старый файл если был
        if ($thesis->document_path) {
            Storage::disk('local')->delete($thesis->document_path);
        }

        $file = $request->file('document');
        $path = $file->store('theses/' . $thesis->id, 'local');

        $thesis->update([
            'document_path' => $path,
            'document_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Документ загружен.');
    }

    // Скачать документ (только участники работы + комиссия + админ)
    public function downloadDocument(Thesis $thesis)
    {
        $this->authorize('downloadDocument', $thesis);

        if (! $thesis->document_path || ! Storage::disk('local')->exists($thesis->document_path)) {
            abort(404, 'Файл не найден.');
        }

        return Storage::disk('local')->download(
            $thesis->document_path,
            $thesis->document_name ?? 'thesis.pdf'
        );
    }

    // Смена статуса - руководитель, комиссия, рецензент
    public function updateStatus(Request $request, Thesis $thesis)
    {
        $this->authorize('updateStatus', $thesis);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Thesis::STATUSES),
        ]);

        $newStatus = $validated['status'];

        // Нельзя вернуть в draft если уже дальше
        if ($newStatus === Thesis::STATUS_INITIAL && $thesis->status !== Thesis::STATUS_INITIAL) {
            return back()->with('error', 'Нельзя вернуть работу в начальный статус.');
        }

        // При финальном статусе - закрываем работу
        if ($newStatus === Thesis::STATUS_FINAL) {
            $request->validate([
                'grade' => 'nullable|integer|min:1|max:5',
            ]);

            $thesis->update([
                'status'  => $newStatus,
                'done_at' => now(),
                'grade'   => $request->input('grade'),
            ]);

            return back()->with('success', 'Работа завершена и закрыта.');
        }

        $thesis->update(['status' => $newStatus]);

        return back()->with('success', 'Статус работы обновлён.');
    }

    // Список всех работ - для руководителя / комиссии / админа
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Thesis::with(['student', 'supervisor', 'topic', 'studyGroup']);

        if ($user->isSupervisor() && ! $user->isAdmin()) {
            // Руководитель видит только свои работы
            $query->where('supervisor_id', $user->id);
        }

        // Фильтры
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('show_completed')) {
            $query->completed();
        } else {
            $query->active();
        }

        if ($request->filled('group')) {
            $query->where('study_group_id', $request->group);
        }

        $theses = $query->latest()->paginate(20)->withQueryString();

        return view('thesis.index', compact('theses'));
    }
}

