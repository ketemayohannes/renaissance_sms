<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Access is gated on the routes (permission:view fees / permission:view payroll).
 */
class FinanceController extends Controller
{
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
