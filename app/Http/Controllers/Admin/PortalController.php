<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view health')->only('health');
        $this->middleware('permission:view inventory')->only('inventory');
    }

    /**
     * Health Records Portal (Placeholder)
     */
    public function health()
    {
        return view('admin.portals.health');
    }

    /**
     * Inventory Management Portal (Placeholder)
     */
    public function inventory()
    {
        return view('admin.portals.inventory');
    }
}
