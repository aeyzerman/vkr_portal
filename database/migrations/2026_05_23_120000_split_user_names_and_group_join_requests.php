<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
            $table->string('first_name')->nullable()->after('last_name');
            $table->string('patronymic')->nullable()->after('first_name');
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            $source = trim((string) ($user->full_name ?: $user->name));
            [$lastName, $firstName, $patronymic] = $this->splitPersonName($source);

            DB::table('users')->where('id', $user->id)->update([
                'last_name' => $lastName,
                'first_name' => $firstName,
                'patronymic' => $patronymic,
                'name' => trim(implode(' ', array_filter([$lastName, $firstName, $patronymic]))) ?: $source,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('full_name');
        });

        Schema::create('study_group_join_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_group_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'study_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_group_join_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name')->nullable()->after('name');
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'full_name' => trim(implode(' ', array_filter([$user->last_name, $user->first_name, $user->patronymic]))),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_name', 'first_name', 'patronymic']);
        });
    }

    /**
     * @return array{0: string, 1: string, 2: ?string}
     */
    private function splitPersonName(string $source): array
    {
        if ($source === '') {
            return ['Пользователь', '', null];
        }

        $parts = preg_split('/\s+/u', $source) ?: [];

        return [
            $parts[0] ?? $source,
            $parts[1] ?? '',
            isset($parts[2]) ? implode(' ', array_slice($parts, 2)) : null,
        ];
    }
};
