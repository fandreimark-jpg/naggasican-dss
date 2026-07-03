<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index()
    {
        $tracks = Track::with('specializations')->orderBy('name')->get();
        return view('principal.tracks', compact('tracks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tracks,name',
            'code' => 'required|string|max:20|unique:tracks,code',
        ]);

        Track::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('principal.tracks')
            ->with('success', 'Track added successfully!');
    }

    public function update(Request $request, $id)
    {
        $track = Track::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:tracks,name,' . $id,
            'code' => 'required|string|max:20|unique:tracks,code,' . $id,
        ]);

        $track->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
        ]);

        return redirect()->route('principal.tracks')
            ->with('success', 'Track updated successfully!');
    }

    public function destroy($id)
    {
        $track = Track::findOrFail($id);

        if ($track->sections()->count() > 0) {
            return redirect()->route('principal.tracks')
                ->with('error', 'Cannot delete track with existing sections.');
        }

        $track->delete();

        return redirect()->route('principal.tracks')
            ->with('success', 'Track deleted successfully!');
    }
}