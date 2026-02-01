<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'employee_profiles';

    protected $fillable = [
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'date_of_birth',
        'personal_email',
        'company_email',
        'phone_number',
        'national_id',
        'passport_number',
        'passport_expiry',
        'kra_pin',
        'nssf_number',
        'nhif_number',
        'marital_status',
        'next_of_kin',
        'job_title',
        'department',
        'job_grade',
        'employment_type',
        'reporting_to',
        'hire_date',
        'confirmation_date',
        'contract_start_date',
        'contract_end_date',
        'termination_date',
        'termination_reason',
        'basic_salary',
        'allowances',
        'deductions',
        'work_location',
        'work_schedule',
        'bank_name',
        'bank_account',
        'bank_branch',
        'passport_copy_path',
        'id_card_copy_path',
        'kra_pin_copy_path',
        'application_letter_path',
        'reference_letter_path',
        'acceptance_letter_path',
        'signed_rules_path',
        'qualifications_path',
        'guarantor_form_path',
        'guarantor_details',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'confirmation_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'termination_date' => 'date',
        'passport_expiry' => 'date',
        'basic_salary' => 'decimal:2',
        'next_of_kin' => 'array',
        'allowances' => 'array',
        'deductions' => 'array',
        'work_schedule' => 'array',
        'guarantor_details' => 'array',
    ];

    // Relationships
    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class,'employee_profile_id');
    }

    public function qualifications()
    {
        return $this->hasMany(EmployeeQualification::class,'employee_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(EmployeeProfile::class, 'reporting_to');
    }

    public function subordinates()
    {
        return $this->hasMany(EmployeeProfile::class, 'reporting_to');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function getFormattedSalaryAttribute()
    {
        return 'Ksh ' . number_format($this->basic_salary, 2);
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? now()->diffInYears($this->date_of_birth) : null;
    }

    public function getServiceYearsAttribute()
    {
        return $this->hire_date ? now()->diffInYears($this->hire_date) : 0;
    }

    public function getNextOfKinNameAttribute()
    {
        return $this->next_of_kin['name'] ?? null;
    }

    public function getGuarantorNameAttribute()
    {
        return $this->guarantor_details['name'] ?? null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    public function scopeOnContract($query)
    {
        return $query->where('employment_type', 'contract');
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('employee_id', 'like', "%{$search}%")
              ->orWhere('national_id', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%");
        });
    }

    // Check if employee has all required documents
    public function hasAllRequiredDocuments()
    {
        $requiredDocs = ['id_card', 'kra_pin', 'passport', 'application', 'reference', 'acceptance', 'rules', 'guarantor'];

        $existingDocs = $this->documents->pluck('document_type')->toArray();

        return count(array_intersect($requiredDocs, $existingDocs)) === count($requiredDocs);
    }

        public function changeLogs(): HasMany
        {
            return $this->hasMany(EmployeeChangeLog::class, 'employee_id');
        }
}
