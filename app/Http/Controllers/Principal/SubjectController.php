<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Track;
use App\Models\Specialization;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['track', 'specialization'])
            ->when(request('type'), fn($q) => $q->where('type', request('type')))
            ->orderBy('grade_level')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $tracks          = Track::with('specializations')->orderBy('name')->get();
        $specializations = Specialization::with('track')->orderBy('name')->get();

        return view('principal.subjects', compact('subjects', 'tracks', 'specializations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:core,elective',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'nullable|exists:tracks,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        Subject::create([
            'name'              => $request->name,
            'type'              => $request->type,
            'grade_level'       => $request->grade_level,
            'track_id'          => $request->type === 'elective' ? $request->track_id : null,
            'specialization_id' => $request->type === 'elective' ? $request->specialization_id : null,
        ]);

        return redirect()->route('principal.subjects')
            ->with('success', 'Subject added successfully!');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:core,elective',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'nullable|exists:tracks,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        $subject->update([
            'name'              => $request->name,
            'type'              => $request->type,
            'grade_level'       => $request->grade_level,
            'track_id'          => $request->type === 'elective' ? $request->track_id : null,
            'specialization_id' => $request->type === 'elective' ? $request->specialization_id : null,
        ]);

        return redirect()->route('principal.subjects')
            ->with('success', 'Subject updated successfully!');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);

        if ($subject->grades()->count() > 0) {
            return redirect()->route('principal.subjects')
                ->with('error', 'Cannot delete subject with existing grades.');
        }

        $subject->delete();

        return redirect()->route('principal.subjects')
            ->with('success', 'Subject deleted successfully!');
    }
}