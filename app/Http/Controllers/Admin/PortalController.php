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
     * Inventory Management Portal — the module is now built; keep this legacy route
     * working by forwarding any old links to the real dashboard.
     */
    public function inventory()
    {
        return redirect()->route('admin.inventory.dashboard');
    }
}
