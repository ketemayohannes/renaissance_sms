<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->enum('kind', ['asset', 'consumable']);
            $table->string('unit')->nullable();            // consumables: pcs, box, ream…
            $table->unsignedInteger('quantity')->default(0); // consumables: running balance from ledger
            $table->unsignedInteger('reorder_level')->nullable(); // consumables: low-stock threshold
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->nullable();
            $table->enum('condition', ['good', 'needs_repair', 'damaged', 'retired'])->default('good');
            $table->enum('status', ['available', 'assigned', 'in_maintenance', 'retired'])->default('available');
            $table->date('purchase_date')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_asset_id')->constrained()->onDelete('cascade');
            // Assigned to an employee OR a free-text location (room/office) — one must be set.
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable(); // null = currently out
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->enum('direction', ['in', 'out']);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 12, 2)->nullable(); // stock-in
            $table->string('supplier')->nullable();          // stock-in
            $table->foreignId('issued_to_employee_id')->nullable()->constrained('employees')->onDelete('set null'); // stock-out
            $table->string('issued_to')->nullable();         // stock-out free-text (department, event…)
            $table->date('movement_date');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_movements');
        Schema::dropIfExists('inventory_assignments');
        Schema::dropIfExists('inventory_assets');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_categories');
    }
};
