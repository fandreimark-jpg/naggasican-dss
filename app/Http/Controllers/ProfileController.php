<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Profile management is disabled in this system.
     * User accounts are managed exclusively by the Principal
     * via User Management (/principal/users).
     */
    public function edit(Request $request): View
    {
        abort(404);
    }

    public function update(Request $request): RedirectResponse
    {
        abort(404);
    }

    public function destroy(Request $request): RedirectResponse
    {
        abort(404);
    }
}