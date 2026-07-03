<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use App\Models\Track;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    public function index()
    {
        $specializations = Specialization::with('track')->orderBy('name')->get();
        $tracks          = Track::orderBy('name')->get();
        return view('principal.specializations', compact('specializations', 'tracks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:20',
        ]);

        Specialization::create([
            'track_id' => $request->track_id,
            'name'     => $request->name,
            'code'     => strtoupper($request->code),
        ]);

        return redirect()->route('principal.specializations')
            ->with('success', 'Specialization added successfully!');
    }

    public function update(Request $request, $id)
    {
        $specialization = Specialization::findOrFail($id);

        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name'     => 'required|string|max:255',
            'code'     => 'required|string|max:20',
        ]);

        $specialization->update([
            'track_id' => $request->track_id,
            'name'     => $request->name,
            'code'     => strtoupper($request->code),
        ]);

        return redirect()->route('principal.specializations')
            ->with('success', 'Specialization updated successfully!');
    }

    public function destroy($id)
    {
        $specialization = Specialization::findOrFail($id);

        if ($specialization->sections()->count() > 0) {
            return redirect()->route('principal.specializations')
                ->with('error', 'Cannot delete specialization with existing sections.');
        }

        $specialization->delete();

        return redirect()->route('principal.specializations')
            ->with('success', 'Specialization deleted successfully!');
    }

    public function byTrack($trackId)
    {
        $specializations = Specialization::where('track_id', $trackId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($specializations);
    }
}