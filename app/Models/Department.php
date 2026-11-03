<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'description', 'head_id', 'is_active'];

    public function head()
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
