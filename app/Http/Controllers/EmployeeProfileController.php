<?php

namespace App\Http\Controllers;

use App\Models\EmployeeProfile;
use App\Models\EmployeeDocument;
use App\Models\EmployeeQualification;
use App\Services\EmployeeChangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeProfileController extends Controller
{
    protected $changeService;
    public function index(Request $request)
    {
        $departments = EmployeeProfile::distinct()
            ->whereNotNull('department')
            ->orderBy('department')
            ->pluck('department');

        $query = EmployeeProfile::with(['documents', 'qualifications', 'supervisor']);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('national_id', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('department') && !empty($request->department)) {
            $query->where('department', $request->department);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('employees_profile.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $supervisors = EmployeeProfile::active()->get();
        return view('employees_profile.create', compact('supervisors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:employee_profiles,national_id|max:20',
            'passport_number' => 'nullable|string|max:50',
            'kra_pin' => 'required|string|unique:employee_profiles,kra_pin|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone_number' => 'required|string|max:15',
            'personal_email' => 'nullable|email',
            'company_email' => 'nullable|email',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',

            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',

            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'job_grade' => 'nullable|string|max:50',
            'employment_type' => 'required|string|in:permanent,contract,probation,casual,intern',
            'reporting_to' => 'nullable|exists:employee_profiles,id',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'work_location' => 'required|string|max:255',

            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',

            'next_of_kin_name' => 'required|string|max:255',
            'next_of_kin_relationship' => 'required|string|max:100',
            'next_of_kin_phone' => 'required|string|max:15',
            'next_of_kin_address' => 'required|string|max:500',

            'guarantor_name' => 'required|string|max:255',
            'guarantor_id_number' => 'required|string|max:20',
            'guarantor_phone' => 'required|string|max:15',
            'guarantor_email' => 'nullable|email',
            'guarantor_address' => 'required|string|max:500',
            'guarantor_relationship' => 'required|string|max:100',

            'passport_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'id_card_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'kra_pin_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'application_letter' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'reference_letter' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'acceptance_letter' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'signed_rules' => 'nullable|file|mimes:pdf|max:2048',
            'guarantor_form' => 'nullable|file|mimes:pdf|max:2048',

            'allowances' => 'nullable|json',
            'deductions' => 'nullable|json',
        ]);

        $employeeId = 'EMP' . date('Y') . Str::random(6);

        $nextOfKin = [
            'name' => $validated['next_of_kin_name'],
            'relationship' => $validated['next_of_kin_relationship'],
            'phone' => $validated['next_of_kin_phone'],
            'address' => $validated['next_of_kin_address']
        ];

        $guarantorDetails = [
            'name' => $validated['guarantor_name'],
            'id_number' => $validated['guarantor_id_number'],
            'phone' => $validated['guarantor_phone'],
            'email' => $validated['guarantor_email'] ?? null,
            'address' => $validated['guarantor_address'],
            'relationship' => $validated['guarantor_relationship']
        ];

        $workSchedule = [
            'monday' => ['start' => '08:00', 'end' => '17:00', 'break' => '13:00-14:00'],
            'tuesday' => ['start' => '08:00', 'end' => '17:00', 'break' => '13:00-14:00'],
            'wednesday' => ['start' => '08:00', 'end' => '17:00', 'break' => '13:00-14:00'],
            'thursday' => ['start' => '08:00', 'end' => '17:00', 'break' => '13:00-14:00'],
            'friday' => ['start' => '08:00', 'end' => '17:00', 'break' => '13:00-14:00'],
            'saturday' => ['start' => null, 'end' => null],
            'sunday' => ['start' => null, 'end' => null]
        ];

        $allowances = $validated['allowances'] ?? [];
        $deductions = $validated['deductions'] ?? [];

        $employee = EmployeeProfile::create([
            'employee_id' => $employeeId,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'national_id' => $validated['national_id'],
            'passport_number' => $validated['passport_number'] ?? null,
            'passport_expiry' => $request->passport_expiry ?? null,
            'kra_pin' => $validated['kra_pin'],
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'phone_number' => $validated['phone_number'],
            'personal_email' => $validated['personal_email'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'nssf_number' => $validated['nssf_number'] ?? null,
            'nhif_number' => $validated['nhif_number'] ?? null,
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
            'job_grade' => $validated['job_grade'] ?? null,
            'employment_type' => $validated['employment_type'],
            'reporting_to' => $validated['reporting_to'] ?? null,
            'basic_salary' => $validated['basic_salary'],
            'hire_date' => $validated['hire_date'],
            'contract_start_date' => $validated['contract_start_date'] ?? null,
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'work_location' => $validated['work_location'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'bank_branch' => $validated['bank_branch'] ?? null,
            'next_of_kin' => $nextOfKin,
            'guarantor_details' => $guarantorDetails,
            'work_schedule' => $workSchedule,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'status' => 'active',
        ]);

        $this->handleDocumentUploads($request, $employee);

        return redirect()->route('employee_profile.show', $employee->id)
            ->with('success', 'Employee created successfully. Employee ID: ' . $employeeId);
    }

    public function show(EmployeeProfile $employee)
    {
        $employee->load(['documents', 'qualifications', 'supervisor', 'subordinates']);
        return view('employees_profile.show', compact('employee'));
    }

    public function edit(EmployeeProfile $employee)
    {
        $supervisors = EmployeeProfile::where('id', '!=', $employee->id)->active()->get();
        return view('employees_profile.edit', compact('employee', 'supervisors'));
    }

    public function update(Request $request, EmployeeProfile $employee)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'phone_number' => 'required|string|max:15',
            'personal_email' => 'nullable|email',
            'company_email' => 'nullable|email',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',

            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',

            'job_title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'job_grade' => 'nullable|string|max:50',
            'employment_type' => 'required|string|in:permanent,contract,probation,casual,intern',
            'reporting_to' => 'nullable|exists:employee_profiles,id',
            'basic_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'confirmation_date' => 'nullable|date',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'work_location' => 'required|string|max:255',
            'status' => 'required|in:active,on_leave,terminated,suspended',

            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',

            'next_of_kin_name' => 'nullable|string|max:255',
            'next_of_kin_relationship' => 'nullable|string|max:100',
            'next_of_kin_phone' => 'nullable|string|max:15',
            'next_of_kin_address' => 'nullable|string|max:500',

            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_id_number' => 'nullable|string|max:20',
            'guarantor_phone' => 'nullable|string|max:15',
            'guarantor_email' => 'nullable|email',
            'guarantor_address' => 'nullable|string|max:500',
            'guarantor_relationship' => 'nullable|string|max:100',

            'termination_date' => 'nullable|date',
            'termination_reason' => 'nullable|string|max:1000',
        ]);

        if ($request->filled('next_of_kin_name')) {
            $nextOfKin = [
                'name' => $validated['next_of_kin_name'],
                'relationship' => $validated['next_of_kin_relationship'],
                'phone' => $validated['next_of_kin_phone'],
                'address' => $validated['next_of_kin_address']
            ];
            $employee->next_of_kin = $nextOfKin;
        }

        if ($request->filled('guarantor_name')) {
            $guarantorDetails = [
                'name' => $validated['guarantor_name'],
                'id_number' => $validated['guarantor_id_number'],
                'phone' => $validated['guarantor_phone'],
                'email' => $validated['guarantor_email'] ?? null,
                'address' => $validated['guarantor_address'],
                'relationship' => $validated['guarantor_relationship']
            ];
            $employee->guarantor_details = $guarantorDetails;
        }

        $employee->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'phone_number' => $validated['phone_number'],
            'personal_email' => $validated['personal_email'] ?? null,
            'company_email' => $validated['company_email'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'nssf_number' => $validated['nssf_number'] ?? null,
            'nhif_number' => $validated['nhif_number'] ?? null,
            'job_title' => $validated['job_title'],
            'department' => $validated['department'],
            'job_grade' => $validated['job_grade'] ?? null,
            'employment_type' => $validated['employment_type'],
            'reporting_to' => $validated['reporting_to'] ?? null,
            'basic_salary' => $validated['basic_salary'],
            'hire_date' => $validated['hire_date'],
            'confirmation_date' => $validated['confirmation_date'] ?? null,
            'contract_start_date' => $validated['contract_start_date'] ?? null,
            'contract_end_date' => $validated['contract_end_date'] ?? null,
            'work_location' => $validated['work_location'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account' => $validated['bank_account'] ?? null,
            'bank_branch' => $validated['bank_branch'] ?? null,
            'status' => $validated['status'],
            'termination_date' => $validated['termination_date'] ?? null,
            'termination_reason' => $validated['termination_reason'] ?? null,
        ]);

        // Handle document uploads if any
        $this->handleDocumentUploads($request, $employee);

        return redirect()->route('approvals.pending', $employee->id)
            ->with('success', 'Employee profile updated successfully');
    }

    private function handleDocumentUploads(Request $request, EmployeeProfile $employee)
    {
        $documentTypes = [
            'passport_copy' => 'passport',
            'id_card_copy' => 'id_card',
            'kra_pin_copy' => 'kra_pin',
            'application_letter' => 'application',
            'reference_letter' => 'reference',
            'acceptance_letter' => 'acceptance',
            'signed_rules' => 'rules',
            'guarantor_form' => 'guarantor'
        ];

        foreach ($documentTypes as $field => $type) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $type . '_' . $employee->employee_id . '_' . time() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('documents/employeesprofile/' . $employee->id, $filename, 'public');

                EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'document_type' => $type,
                    'document_name' => ucfirst(str_replace('_', ' ', $field)),
                    'file_path' => $path,
                    'is_verified' => false
                ]);

                $fieldMap = [
                    'passport_copy' => 'passport_copy_path',
                    'id_card_copy' => 'id_card_copy_path',
                    'kra_pin_copy' => 'kra_pin_copy_path',
                    'application_letter' => 'application_letter_path',
                    'reference_letter' => 'reference_letter_path',
                    'acceptance_letter' => 'acceptance_letter_path',
                    'signed_rules' => 'signed_rules_path',
                    'guarantor_form' => 'guarantor_form_path'
                ];

                if (isset($fieldMap[$field])) {
                    $employee->update([$fieldMap[$field] => $path]);
                }
            }
        }
    }
    // Add qualification
    public function addQualification(Request $request, EmployeeProfile $employee)
    {
        $validated = $request->validate([
            'qualification_type' => 'required|in:academic,professional,skill',
            'title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'grade' => 'nullable|string|max:50',
            'year_obtained' => 'required|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $filename = 'qualification_' . Str::slug($validated['title']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $certificatePath = $file->storeAs('documents/qualifications/' . $employee->id, $filename, 'public');
        }

        $employee->qualifications()->create([
            'qualification_type' => $validated['qualification_type'],
            'title' => $validated['title'],
            'institution' => $validated['institution'],
            'grade' => $validated['grade'],
            'year_obtained' => $validated['year_obtained'],
            'certificate_path' => $certificatePath
        ]);

        return back()->with('success', 'Qualification added successfully');
    }

    // Terminate employee
    public function terminate(Request $request, EmployeeProfile $employee)
    {
        $validated = $request->validate([
            'termination_date' => 'required|date',
            'termination_reason' => 'required|string|max:1000'
        ]);

        $employee->update([
            'termination_date' => $validated['termination_date'],
            'termination_reason' => $validated['termination_reason'],
            'status' => 'terminated',
            'contract_end_date' => $validated['termination_date']
        ]);

        return back()->with('success', 'Employee terminated successfully');
    }

    // Download document
    public function downloadDocument(EmployeeDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($document->file_path);
        $filename = basename($document->file_path);

        return response()->download($path, $filename);
    }
    // View PDF document
    public function viewDocument(EmployeeDocument $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        $path = Storage::disk('public')->path($document->file_path);
        return response()->file($path);
    }

}
