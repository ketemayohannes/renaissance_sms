<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryBorrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'user_id',
        'issued_date',
        'returned_date',
        'status',
        'remarks',
        'issued_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'returned_date' => 'date',
    ];

    public function book()
    {
        return $this->belongsTo(LibraryBook::class, 'book_id');
    }

    public function borrower()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Currently checked-out copies — the borrowing has no return date yet.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('returned_date');
    }
}
