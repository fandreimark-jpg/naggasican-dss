<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Section;
use App\Models\Grade;
use App\Models\RiskResult;
use App\Models\User;
use App\Models\Track;
use App\Models\Specialization;
use App\Models\Subject;
use App\Helpers\LogActivity;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PrincipalController extends Controller
{
    // =============================================
    // DASHBOARD
    // =============================================
    public function dashboard()
    {
        $totalStudents = Student::count();
        $totalSections = Section::count();
        $totalAdvisers = User::where('role', 'adviser')->count();

        $latestPerStudent = RiskResult::whereIn('id',
            RiskResult::selectRaw('MAX(id) as id')
                ->groupBy('student_id')
                ->pluck('id')
        )->get();

        $lowRisk      = $latestPerStudent->where('risk_level', 'low')->count();
        $moderateRisk = $latestPerStudent->where('risk_level', 'moderate')->count();
        $highRisk     = $latestPerStudent->where('risk_level', 'high')->count();

        $sections = Section::with([
            'students.grades',
            'students.riskResults',
            'adviser',
            'track',
            'specialization',
        ])->get();

        // ✅ Performance Trend — average per term
        $termTrends = [];
        foreach ([1, 2, 3] as $term) {
            $avg = Grade::where('grading_period', $term)->avg('grade');
            $termTrends[] = $avg ? round($avg, 2) : null;
        }

        // ✅ At-risk students — moderate + high only
        $atRiskStudents = Student::with(['section', 'riskResults'])
            ->whereHas('riskResults', function ($q) {
                $q->whereIn('risk_level', ['moderate', 'high']);
            })
            ->get()
            ->map(function ($student) {
                $latestRisk = $student->riskResults->sortByDesc('grading_period')->first();
                return [
                    'name'       => $student->last_name . ', ' . $student->first_name,
                    'section'    => $student->section->name ?? '—',
                    'average'    => $latestRisk->average_grade ?? '—',
                    'risk_level' => $latestRisk->risk_level ?? '—',
                ];
            });

        // ✅ Risk distribution per section — para sa bar chart
        $sectionRiskData = $sections->map(function ($section) {
            $low = $moderate = $high = 0;
            foreach ($section->students as $student) {
                $latest = $student->riskResults->sortByDesc('grading_period')->first();
                if ($latest) {
                    match($latest->risk_level) {
                        'low'      => $low++,
                        'moderate' => $moderate++,
                        'high'     => $high++,
                        default    => null,
                    };
                }
            }
            return [
                'section'  => $section->name,
                'low'      => $low,
                'moderate' => $moderate,
                'high'     => $high,
            ];
        });

        // ✅ Honors — base sa latest average_grade ng risk_results
        $allStudentsWithRisk = Student::with(['section', 'riskResults'])
            ->whereHas('riskResults')
            ->get()
            ->map(function ($student) {
                $latest = $student->riskResults->sortByDesc('grading_period')->first();
                return [
                    'name'    => $student->last_name . ', ' . $student->first_name,
                    'section' => $student->section->name ?? '—',
                    'average' => $latest->average_grade ?? null,
                ];
            })
            ->filter(fn($s) => $s['average'] !== null);

        $highestHonors = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 98)->values();
        $highHonors    = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 95 && $s['average'] < 98)->values();
        $withHonors    = $allStudentsWithRisk->filter(fn($s) => $s['average'] >= 90 && $s['average'] < 95)->values();

        return view('principal.dashboard', compact(
            'totalStudents', 'totalSections', 'totalAdvisers',
            'lowRisk', 'moderateRisk', 'highRisk',
            'sections', 'termTrends', 'atRiskStudents', 'sectionRiskData',
            'highestHonors', 'highHonors', 'withHonors'
        ));
    }

    // =============================================
    // USER MANAGEMENT
    // =============================================
    public function users()
    {
        $users = User::where('id', '!=', auth()->id())
            ->with('section')
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10);

        return view('principal.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'username'    => 'required|string|max:255|alpha_dash',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:adviser,principal',
        ]);

        $email = $request->username . '@naggasican.edu.ph';

        if (User::where('email', $email)->exists()) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        $fullName = $request->last_name . ', ' . $request->first_name;

        User::create([
            'name'        => $fullName,
            'last_name'   => $request->last_name,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'email'       => $email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
        ]);

        LogActivity::log(
            'create_user',
            'Created ' . $request->role . ' account: ' . $request->last_name . ', ' . $request->first_name,
            'users',
            null
        );

        return redirect()->route('principal.users')
            ->with('success', ucfirst($request->role) . ' account created successfully!');
    }

    public function editUser($id)
    {
        $user     = User::where('id', $id)->where('role', 'adviser')->firstOrFail();
        $sections = Section::all();
        return view('principal.users-edit', compact('user', 'sections'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'username'    => 'required|string|max:255|alpha_dash',
            'password'    => 'nullable|string|min:8',
            'role'        => 'required|in:adviser,principal',
        ]);

        $email = $request->username . '@naggasican.edu.ph';

        if (User::where('email', $email)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['username' => 'This username is already taken.'])->withInput();
        }

        $fullName = $request->last_name . ', ' . $request->first_name;

        $user->update([
            'name'        => $fullName,
            'last_name'   => $request->last_name,
            'first_name'  => $request->first_name,
            'middle_name' => $request->middle_name,
            'email'       => $email,
            'password'    => $request->password
                ? Hash::make($request->password)
                : $user->password,
            'role'        => $request->role,
        ]);

        return redirect()->route('principal.users')
            ->with('success', 'User updated successfully!');
    }

    public function destroyUser($id)
    {
        $user = User::where('id', $id)->firstOrFail();

        if ($user->id === auth()->id()) {
            return redirect()->route('principal.users')
                ->with('error', 'You cannot delete your own account.');
        }

        Section::where('adviser_id', $user->id)->update(['adviser_id' => null]);
        Grade::where('encoded_by', $user->id)->update(['encoded_by' => null]);
        $user->delete();

        return redirect()->route('principal.users')
            ->with('success', 'User removed successfully!');
    }

    // =============================================
    // TRACKS MANAGEMENT
    // =============================================
    public function tracks()
    {
        $tracks = Track::with('specializations')->orderBy('name')->get();
        return view('principal.tracks', compact('tracks'));
    }

    public function storeTrack(Request $request)
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

    public function updateTrack(Request $request, $id)
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

    public function destroyTrack($id)
    {
        $track = Track::findOrFail($id);

        // Hindi pwedeng burahin kung may sections na gumagamit
        if ($track->sections()->count() > 0) {
            return redirect()->route('principal.tracks')
                ->with('error', 'Cannot delete track with existing sections.');
        }

        $track->delete();

        return redirect()->route('principal.tracks')
            ->with('success', 'Track deleted successfully!');
    }

    // =============================================
    // SPECIALIZATIONS MANAGEMENT
    // =============================================
    public function specializations()
    {
        $specializations = Specialization::with('track')->orderBy('name')->get();
        $tracks          = Track::orderBy('name')->get();
        return view('principal.specializations', compact('specializations', 'tracks'));
    }

    public function storeSpecialization(Request $request)
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

    public function updateSpecialization(Request $request, $id)
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

    public function destroySpecialization($id)
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

    // =============================================
    // SUBJECTS MANAGEMENT
    // =============================================
    public function subjects()
    {
        $subjects = Subject::with(['track', 'specialization'])
            ->orderBy('grade_level')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $tracks          = Track::with('specializations')->orderBy('name')->get();
        $specializations = Specialization::with('track')->orderBy('name')->get();

        return view('principal.subjects', compact('subjects', 'tracks', 'specializations'));
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:core,elective',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'nullable|exists:tracks,id',
            'specialization_id' => 'nullable|exists:specializations,id',
        ]);

        // Core subjects — walang track o specialization
        // Elective subjects — may track at optional na specialization
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

    public function updateSubject(Request $request, $id)
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

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);

        // Hindi pwedeng burahin kung may grades na naka-encode
        if ($subject->grades()->count() > 0) {
            return redirect()->route('principal.subjects')
                ->with('error', 'Cannot delete subject with existing grades.');
        }

        $subject->delete();

        return redirect()->route('principal.subjects')
            ->with('success', 'Subject deleted successfully!');
    }

    // =============================================
    // SECTIONS MANAGEMENT
    // =============================================
    public function sections()
    {
        $sections = Section::with(['adviser', 'students', 'track', 'specialization'])
            ->orderBy('grade_level')
            ->get();

        $availableAdvisers = User::where('role', 'adviser')
            ->with("section")
            ->orderBy('last_name')
            ->get();

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

    public function storeSection(Request $request)
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

        LogActivity::log(
            'create_section',
            'Created section: ' . $request->name,
            'sections',
            null
        );

        return redirect()->route('principal.sections')
            ->with('success', 'Section created successfully!');
    }

    public function updateSection(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        $adviserId = $request->adviser_id ?: null;

        $request->validate([
            'name'              => 'required|string|max:255',
            'grade_level'       => 'required|in:11,12',
            'track_id'          => 'required|exists:tracks,id',
            'specialization_id' => 'required|exists:specializations,id',
            'school_year'       => 'required|string|max:20',
            'adviser_id'        => 'nullable|exists:users,id',
        ]);

        // Dagdag na check — kung may adviser na assigned sa ibang section
        if ($adviserId) {
            $existingSection = Section::where('adviser_id', $adviserId)
                ->where('id', '!=', $id)
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

    public function destroySection($id)
    {
        $section = Section::findOrFail($id);

        if ($section->students()->count() > 0) {
            return redirect()->route('principal.sections')
                ->with('error', 'Cannot delete section with existing students.');
        }

        $section->delete();

        return redirect()->route('principal.sections')
            ->with('success', 'Section deleted successfully!');
    }

    // =============================================
    // AJAX — Specializations by Track
    // =============================================
    public function getSpecializationsByTrack($trackId)
    {
        $specializations = Specialization::where('track_id', $trackId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json($specializations);
    }

    // =============================================
    // STUDENTS MANAGEMENT
    // =============================================
    public function students()
    {
        $query = Student::with(['section'])
            ->orderBy('last_name');

        if (request('section_id')) {
            $query->where('section_id', request('section_id'));
        }

        $students = $query->paginate(10);
        $sections = Section::with(['track', 'specialization'])
            ->orderBy('grade_level')
            ->get();

        return view('principal.students', compact('students', 'sections'));
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'lrn'         => 'required|digits:12|unique:students,lrn',
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender'      => 'required|in:male,female',
            'birthdate'   => 'nullable|date',
            'section_id'  => 'required|exists:sections,id',
        ]);

        Student::create($request->only([
            'lrn', 'last_name', 'first_name',
            'middle_name', 'gender', 'birthdate', 'section_id'
        ]));

        LogActivity::log(
            'add_student',
            'Added student: ' . $request->last_name . ', ' . $request->first_name,
            'students',
            null
        );

        return redirect()->route('principal.students')
            ->with('success', 'Student added successfully!');
    }

    public function updateStudent(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'lrn'         => 'required|digits:12|unique:students,lrn,' . $student->id,
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender'      => 'required|in:male,female',
            'birthdate'   => 'nullable|date',
            'section_id'  => 'required|exists:sections,id',
        ]);

        $student->update($request->only([
            'lrn', 'last_name', 'first_name',
            'middle_name', 'gender', 'birthdate', 'section_id'
        ]));

        return redirect()->route('principal.students')
            ->with('success', 'Student updated successfully!');
    }

    public function destroyStudent($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();

        LogActivity::log(
            'delete_student',
            'Removed student: ' . $student->last_name . ', ' . $student->first_name,
            'students',
            $id
        );

        return redirect()->route('principal.students')
            ->with('success', 'Student removed successfully!');
    }

    // =============================================
    // REPORTS
    // =============================================
    public function reports()
    {
        $sections = Section::with([
                        'adviser',
                        'track',
                        'specialization',
                        'students.grades',
                        'students.riskResults',
                        'reportSubmissions', // ✅ add this
                    ])
                    ->orderBy('grade_level')
                    ->get();

        return view('principal.reports', compact('sections'));
    }

    public function activityLogs()
    {
        $logs = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('principal.activity-logs', compact('logs'));
    }
}