<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Access is gated on the routes (permission:view health / permission:view inventory).
 */
class PortalController extends Controller
{
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
