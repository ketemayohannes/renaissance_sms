<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view fees')->only('fees');
        $this->middleware('permission:view payroll')->only('payroll');
    }

    /**
     * Fee Management Dashboard (Placeholder)
     */
    public function fees()
    {
        return view('admin.finance.fees');
    }

    /**
     * Payroll Dashboard (Placeholder)
     */
    public function payroll()
    {
        return view('admin.finance.payroll');
    }
}
