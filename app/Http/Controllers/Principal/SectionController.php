<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Track;
use App\Models\Specialization;
use App\Models\User;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;

/**
 * SectionController (Principal)
 *
 * Manages class sections — creating, updating, and deleting sections.
 * Each section is assigned to one adviser and belongs to one track/specialization.
 */
class SectionController extends Controller
{
    /**
     * Show all sections with their related data.
     * Available advisers = advisers who are NOT yet assigned to any section.
     */
    public function index()
    {
        $sections = Section::with(['adviser', 'students', 'track', 'specialization'])
            ->orderBy('grade_level')
            ->get();

        // Get IDs of advisers already assigned to a section
        $assignedAdviserIds = Section::whereNotNull('adviser_id')
            ->pluck('adviser_id')
            ->toArray();

        // Only show unassigned advisers in the Add dropdown
        // Prevents assigning one adviser to multiple sections
        $availableAdvisers = User::where('role', 'adviser')
            ->whereNotIn('id', $assignedAdviserIds)
            ->orderBy('last_name')
            ->get();

        // All advisers shown in Edit dropdown (including currently assigned)
        $allAdvisers = User::where('role', 'adviser')
            ->orderBy('last_name')
            ->get();

        $tracks          = Track::with('specializations')->orderBy('name')->get();
        $specializations = Specialization::with('track')->orderBy('name')->get();

        return view('principal.sections', compact(
            'sections', 'availableAdvisers', 'allAdvisers',
            'tracks', 'specializations'
        ));
    }

    /**
     * Create a new section.
     * Adviser assignment is optional — section can exist without an adviser.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'required|exists:tracks,id',
            'specialization_id' => 'required|exists:specializations,id',
            'school_year'       => 'required|string|max:20',
            'adviser_id'        => 'nullable|exists:users,id',
        ]);

        Section::create([
            'name'              => $request->name,
            'grade_level'       => $request->grade_level,
            'track_id'          => $request->track_id,
            'specialization_id' => $request->specialization_id,
            'school_year'       => $request->school_year,
            'adviser_id'        => $request->adviser_id ?: null,
        ]);

        LogActivity::log('create_section', 'Created section: ' . $request->name, 'sections', null);

        return redirect()->route('principal.sections')
            ->with('success', 'Section created successfully!');
    }

    /**
     * Update an existing section.
     * Checks if the selected adviser is already assigned to another section.
     */
    public function update(Request $request, $id)
    {
        $section   = Section::findOrFail($id);
        $adviserId = $request->adviser_id ?: null;

        $request->validate([
            'name'              => 'required|string|max:255',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'required|exists:tracks,id',
            'specialization_id' => 'required|exists:specializations,id',
            'school_year'       => 'required|string|max:20',
            'adviser_id'        => 'nullable|exists:users,id',
        ]);

        // Prevent assigning an adviser who is already assigned to another section
        if ($adviserId) {
            $existingSection = Section::where('adviser_id', $adviserId)
                ->where('id', '!=', $id) // exclude current section from check
                ->first();

            if ($existingSection) {
                return back()->withErrors([
                    'adviser_id' => 'This adviser is already assigned to Section ' . $existingSection->name . '.'
                ])->withInput();
            }
        }

        $section->update([
            'name'              => $request->name,
            'grade_level'       => $request->grade_level,
            'track_id'          => $request->track_id,
            'specialization_id' => $request->specialization_id,
            'school_year'       => $request->school_year,
            'adviser_id'        => $request->adviser_id ?: null,
        ]);

        return redirect()->route('principal.sections')
            ->with('success', 'Section updated successfully!');
    }

    /**
     * Delete a section.
     * Cannot delete a section that still has students enrolled.
     */
    public function destroy($id)
    {
        $section = Section::findOrFail($id);

        // Prevent deletion if section has students
        if ($section->students()->count() > 0) {
            return redirect()->route('principal.sections')
                ->with('error', 'Cannot delete section with existing students.');
        }

        $section->delete();

        return redirect()->route('principal.sections')
            ->with('success', 'Section deleted successfully!');
    }
}