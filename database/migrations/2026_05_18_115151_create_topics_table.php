<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('title');                         // Название темы ВКР
            $table->text('description')->nullable();         // Описание / задание
            $table->foreignId('proposed_by')                 // Кто предложил (студент или руководитель)
            ->constrained('users')
                ->restrictOnDelete();
            // Если тему предложил руководитель - он может её "зарезервировать" под конкретного студента
            $table->foreignId('reserved_for')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->boolean('is_approved')->default(false);  // Одобрена кафедрой
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
