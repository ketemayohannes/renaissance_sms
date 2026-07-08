<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Workflow A: staff request an item from existing stock.
        // pending -> approved (Principal) -> fulfilled (Inventory Manager); or rejected / cancelled.
        Schema::create('inventory_item_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('quantity');
            $table->text('purpose');
            $table->enum('status', ['pending', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('pending');

            // Principal decision
            $table->foreignId('decided_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('decision_remarks')->nullable();
            $table->timestamp('decided_at')->nullable();

            // Inventory Manager fulfillment — links back to whichever engine record it produced.
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('stock_movement_id')->nullable()->constrained('inventory_stock_movements')->onDelete('set null');
            $table->foreignId('assignment_id')->nullable()->constrained('inventory_assignments')->onDelete('set null');

            $table->timestamps();
        });

        // Workflow B: request to purchase something (may not exist in the catalog yet).
        // pending -> pending_gm (Principal approved) -> approved / declined (GM); or principal_declined.
        Schema::create('inventory_purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->onDelete('set null'); // restock of an existing item
            $table->foreignId('inventory_category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');           // free text (new item) or copied from the linked item
            $table->unsignedInteger('quantity');
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_cost', 12, 2)->nullable();
            $table->text('justification');
            $table->enum('status', ['pending', 'pending_gm', 'principal_declined', 'approved', 'declined'])->default('pending');

            // Stage 1: Principal
            $table->foreignId('principal_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('principal_remarks')->nullable();
            $table->timestamp('principal_decided_at')->nullable();

            // Stage 2: General Manager (final)
            $table->foreignId('gm_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('gm_remarks')->nullable();
            $table->timestamp('gm_decided_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_requests');
        Schema::dropIfExists('inventory_item_requests');
    }
};
