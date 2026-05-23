<?php

use App\Enums\ThesisAssignmentStatus;
use App\Enums\ThesisAssignmentType;
use App\Enums\ThesisStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();

            // Участники
            $table->foreignId('student_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('supervisor_id')               // Научный руководитель
            ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('study_group_id')
                ->constrained('study_groups')
                ->restrictOnDelete();

            $table->foreignId('topic_id')
                ->nullable()                               // Тема может быть не выбрана ещё
                ->constrained('topics')
                ->nullOnDelete();

            // Как тема попала к студенту
            $table->unsignedSmallInteger('assignment_type')->default(ThesisAssignmentType::None->value)->comment('See ' . ThesisAssignmentType::class);

            // Состояние согласования темы
            $table->unsignedSmallInteger('assignment_status')->default(ThesisAssignmentStatus::None->value)->comment('See ' . ThesisAssignmentStatus::class);

            $table->timestamp('assigned_at')->nullable();    // Когда тема была предложена / назначена
            $table->timestamp('assignment_responded_at')->nullable(); // Когда студент ответил на предложение
            $table->timestamp('started_at')->nullable();     // Когда студент начал работу
            $table->timestamp('submitted_at')->nullable();   // Когда работа была сдана

            // Статус / стадия работы
            // draft      - начальный, работа только создана
            // submitted  - студент сдал на проверку
            // review     - на рецензировании
            // revision   - отправлена на доработку
            // approved   - одобрена руководителем
            // completed  - финальный, защищена
            $table->unsignedSmallInteger('status')->default(ThesisStatus::None->value)->comment('See ' . ThesisStatus::class);

            // Загруженный документ (путь в storage)
            $table->string('document_path')->nullable();
            $table->string('document_name')->nullable();     // Оригинальное имя файла

            // Финализация - когда работа завершена (done_at заполнен = неактивна в фильтрах)
            $table->timestamp('done_at')->nullable();

            // Оценка на защите
            $table->unsignedTinyInteger('grade')->nullable();

            $table->timestamps();

            // Индекс для быстрой выборки активных работ студента
            $table->index(['student_id', 'done_at']);
            $table->index(['study_group_id', 'assignment_status']);
            $table->index(['supervisor_id', 'assignment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};
