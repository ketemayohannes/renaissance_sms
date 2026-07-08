<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryBook extends Model
{
    use HasFactory;

    public const TYPES = ['physical', 'digital'];

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'category',
        'type',
        'quantity',
        'available_copies',
        'shelf_location',
        'file_path',
        'file_format',
        'cover_image',
        'description',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'available_copies' => 'integer',
        'is_active' => 'boolean',
    ];

    public function borrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, 'book_id');
    }

    /**
     * Copies currently checked out (open borrowings, i.e. not yet returned).
     */
    public function activeBorrowings()
    {
        return $this->hasMany(LibraryBorrowing::class, 'book_id')->whereNull('returned_date');
    }

    public function getIsPhysicalAttribute(): bool
    {
        return $this->type === 'physical';
    }

    public function getIsDigitalAttribute(): bool
    {
        return $this->type === 'digital';
    }

    /**
     * A physical book that has at least one copy free to check out.
     */
    public function scopeAvailable($query)
    {
        return $query->where('type', 'physical')->where('available_copies', '>', 0);
    }
}
