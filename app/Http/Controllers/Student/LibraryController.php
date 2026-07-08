<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\BrowsesLibraryCatalog;
use App\Http\Controllers\Controller;

class LibraryController extends Controller
{
    use BrowsesLibraryCatalog;

    protected function libraryViewPrefix(): string
    {
        return 'student.library';
    }
}
