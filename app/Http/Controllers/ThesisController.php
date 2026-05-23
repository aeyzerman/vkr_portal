<?php

namespace App\Http\Controllers;

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisStatus;
use App\Models\StudyGroup;
use App\Models\Thesis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ThesisController extends Controller
{
    public function my(Request $request)
    {
        $user = $request->user();

        $thesis = $user->activeThesis()
            ->with(['topic', 'supervisor', 'studyGroup'])
            ->first();

        $pendingOffers = $user->topicOffers()
            ->with(['topic', 'supervisor', 'studyGroup'])
            ->latest('assigned_at')
            ->get();

        $history = $user->theses()
            ->whereNotNull('done_at')
            ->with(['topic', 'studyGroup', 'supervisor'])
            ->latest('done_at')
            ->get();

        return view('thesis.my', compact('thesis', 'pendingOffers', 'history'));
    }

    public function show(Thesis $thesis)
    {
        $this->authorize('view', $thesis);

        $thesis->load(['student.studyGroup', 'supervisor', 'topic', 'studyGroup']);

        return view('thesis.show', compact('thesis'));
    }

    public function uploadDocument(Request $request, Thesis $thesis)
    {
        $this->authorize('uploadDocument', $thesis);

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx|max:20480',
        ]);

        if ($thesis->document_path) {
            Storage::disk('local')->delete($thesis->document_path);
        }

        $file = $request->file('document');
        $path = $file->store('theses/' . $thesis->id, 'local');

        $thesis->update([
            'document_path' => $path,
            'document_name' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Файл работы загружен.');
    }

    public function downloadDocument(Thesis $thesis)
    {
        $this->authorize('downloadDocument', $thesis);

        if (! $thesis->document_path || ! Storage::disk('local')->exists($thesis->document_path)) {
            abort(404, 'Файл не найден.');
        }

        return Storage::disk('local')->download(
            $thesis->document_path,
            $thesis->document_name ?? 'thesis-document'
        );
    }

    public function acceptOffer(Request $request, Thesis $thesis)
    {
        abort_unless($thesis->student_id === $request->user()->id, 403);

        if (! $thesis->isAwaitingStudentDecision()) {
            return back()->with('error', 'Это предложение уже неактуально.');
        }

        if ($request->user()->activeThesis()->exists()) {
            return back()->with('error', 'У студента уже есть активная работа.');
        }

        $thesis->update([
            'assignment_status' => ThesisAssignmentStatus::Accepted,
            'assignment_responded_at' => now(),
            'started_at' => now(),
        ]);

        Thesis::query()
            ->where('student_id', $thesis->student_id)
            ->where('id', '!=', $thesis->id)
            ->whereNull('done_at')
            ->where('assignment_status', ThesisAssignmentStatus::Pending->value)
            ->update([
                'assignment_status' => ThesisAssignmentStatus::Declined->value,
                'assignment_responded_at' => now(),
                'done_at' => now(),
            ]);

        if ($thesis->topic) {
            $thesis->topic->update(['reserved_for' => null]);
        }

        return redirect()->route('thesis.my')
            ->with('success', 'Предложение темы принято.');
    }

    public function declineOffer(Request $request, Thesis $thesis)
    {
        abort_unless($thesis->student_id === $request->user()->id, 403);

        if (! $thesis->isAwaitingStudentDecision()) {
            return back()->with('error', 'Это предложение уже неактуально.');
        }

        $thesis->update([
            'assignment_status' => ThesisAssignmentStatus::Declined,
            'assignment_responded_at' => now(),
            'done_at' => now(),
        ]);

        if ($thesis->topic) {
            $thesis->topic->update(['reserved_for' => null]);
        }

        return redirect()->route('thesis.my')
            ->with('success', 'Предложение темы отклонено.');
    }

    public function updateStatus(Request $request, Thesis $thesis)
    {
        $this->authorize('updateStatus', $thesis);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(
                static fn(ThesisStatus $status) => $status->value,
                array_filter(ThesisStatus::cases(), static fn(ThesisStatus $status) => $status !== ThesisStatus::None)
            ))],
            'grade' => 'nullable|integer|min:2|max:5',
        ]);

        $status = ThesisStatus::from((int) $validated['status']);
        $updates = ['status' => $status];

        if ($status === ThesisStatus::Submitted) {
            $updates['submitted_at'] = now();
        }

        if ($status === Thesis::STATUS_FINAL) {
            $updates['done_at'] = now();
            $updates['grade'] = $validated['grade'] ?? null;
        }

        $thesis->update($updates);
        $thesis->refresh();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Статус работы обновлён.',
                'thesis' => [
                    'id' => $thesis->id,
                    'status' => $thesis->status->value,
                    'status_label' => $thesis->status->label(),
                ],
            ]);
        }

        return back()->with('success', 'Статус работы обновлён.');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Thesis::query()
            ->with(['student.studyGroup', 'supervisor', 'topic', 'studyGroup'])
            ->whereIn('assignment_status', ThesisAssignmentStatus::activeValues())
            ->orderByDesc('updated_at');

        if ($user->isSupervisor() && ! $user->isAdmin()) {
            $query->where('supervisor_id', $user->id);
        } elseif (! ($user->isAdmin() || $user->isCommission() || $user->isReviewer())) {
            return redirect()->route('thesis.my');
        }

        if ($request->boolean('show_completed')) {
            $query->whereNotNull('done_at');
        } else {
            $query->whereNull('done_at');
        }

        if ($request->filled('group')) {
            $query->where('study_group_id', $request->integer('group'));
        }

        $theses = $query->get();

        $thesesByStatus = [];
        foreach (ThesisStatus::boardColumns() as $column) {
            $thesesByStatus[$column->value] = collect();
        }

        foreach ($theses as $thesis) {
            $column = ThesisStatus::forBoard($thesis->status?->value);
            $thesesByStatus[$column->value]->push($thesis);
        }

        $groups = StudyGroup::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('thesis.index', [
            'columns' => ThesisStatus::boardColumns(),
            'thesesByStatus' => $thesesByStatus,
            'groups' => $groups,
        ]);
    }
}
