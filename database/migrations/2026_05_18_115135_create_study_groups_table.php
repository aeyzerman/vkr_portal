<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Название группы, напр. "ИВТ-21"
            $table->unsignedTinyInteger('course');           // Курс: 1-6
            $table->string('specialty_code');                // Код специальности, напр. "09.03.01"
            $table->string('specialty_name');                // Название специальности
            $table->foreignId('supervisor_id')               // Руководитель группы (куратор)
            ->constrained('users')
                ->restrictOnDelete();
            $table->year('enrollment_year');                 // Год поступления потока
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_groups');
    }
};
