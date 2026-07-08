<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Library Books (Physical & Digital)
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn')->nullable();
            $table->string('publisher')->nullable();
            $table->string('category')->nullable(); // Fiction, Science, etc.
            $table->enum('type', ['physical', 'digital'])->default('physical');

            // Physical copies — checked out and back via library_borrowings.
            $table->integer('quantity')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('shelf_location')->nullable();

            // Digital resource — a hosted file (PDF/EPUB), available while active. No borrowing.
            $table->string('file_path')->nullable();
            $table->string('file_format')->nullable(); // PDF, EPUB

            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Borrowing Records — simple check-out / check-in only (no due dates, no fines).
        Schema::create('library_borrowings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // the borrower
            $table->date('issued_date');
            $table->date('returned_date')->nullable(); // null = currently checked out
            $table->enum('status', ['borrowed', 'returned'])->default('borrowed');
            $table->text('remarks')->nullable();
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade'); // librarian who issued it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_borrowings');
        Schema::dropIfExists('library_books');
    }
};
