<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Academic calendar: the single source of truth for academic weeks, school days,
 * examination windows, breaks and holidays. Builds on the existing
 * academic_years + terms (semester/quarter) tables instead of duplicating them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            // Ethiopian (E.C.) year this academic year corresponds to, e.g. 2019.
            $table->unsignedSmallInteger('ethiopian_year')->nullable()->after('name');
            // ISO weekdays (1 = Mon … 7 = Sun) that are normally working days.
            $table->json('working_weekdays')->nullable()->after('end_date');
        });

        // One row per academic week, exactly as published in the official calendar.
        Schema::create('academic_weeks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            // Quarter the week belongs to (null for weeks outside any quarter).
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->unsignedTinyInteger('week_number');   // number within the quarter, as in the source
            $table->unsignedSmallInteger('sequence');      // running order across the year
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('working_days')->nullable(); // as published
            $table->string('week_type', 20)->default('instructional'); // instructional | break
            $table->string('description')->nullable();
            $table->string('source_label')->nullable();     // original Ethiopian date range text
            $table->string('source_key')->nullable();       // idempotent import key
            $table->boolean('needs_review')->default(false);
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->unique(['academic_year_id', 'sequence']);
            $table->unique(['academic_year_id', 'source_key']);
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('academic_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->foreignId('academic_week_id')->nullable()->constrained('academic_weeks')->nullOnDelete();
            $table->string('category', 40);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            // false = school is closed for instruction on these days (break, holiday, closure)
            $table->boolean('is_instructional')->default(true);
            $table->unsignedTinyInteger('priority')->default(1); // 0 low · 1 normal · 2 major milestone
            $table->string('status', 20)->default('active');      // active | cancelled
            $table->string('source', 20)->default('manual');      // import | inferred | manual
            $table->string('source_key')->nullable();
            $table->text('source_text')->nullable();              // original wording from the source document
            $table->boolean('needs_review')->default(false);
            $table->text('review_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'source_key']);
            $table->index(['academic_year_id', 'start_date', 'end_date']);
            $table->index(['category', 'status']);
        });

        // Explicit per-date decisions that beat the weekly pattern (e.g. a Saturday
        // make-up day, an emergency closure on a single day).
        Schema::create('school_day_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->boolean('is_school_day');
            $table->string('reason');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['academic_year_id', 'date']);
        });

        $permission = Permission::firstOrCreate(['name' => 'manage academic calendar', 'guard_name' => 'web']);
        foreach (['Super Admin', 'Principal', 'Vice Principal', 'IT / System Admin', 'Registrar'] as $roleName) {
            Role::where('name', $roleName)->first()?->givePermissionTo($permission);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('school_day_overrides');
        Schema::dropIfExists('academic_events');
        Schema::dropIfExists('academic_weeks');

        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn(['ethiopian_year', 'working_weekdays']);
        });

        Permission::where('name', 'manage academic calendar')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
