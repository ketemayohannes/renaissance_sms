<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Categorize Super Admin as administrative
        Role::where('name', 'Super Admin')->update(['category' => 'administrative']);
        
        // Ensure IT / System Admin is also administrative
        Role::where('name', 'IT / System Admin')->update(['category' => 'administrative']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not strictly necessary to undo for data consistency
    }
};
