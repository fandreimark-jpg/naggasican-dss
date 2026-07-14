<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Registration is disabled.
     * User accounts are created exclusively by the Admin
     * via User Management (/admin/users).
     */
    public function create(): View
    {
        abort(404);
    }

    public function store(Request $request): RedirectResponse
    {
        abort(404);
    }
}