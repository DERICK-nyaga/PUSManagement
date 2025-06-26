<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class ReportController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $reports = Report::with('user')
            ->when(Auth::user()->role !== 'admin', function($query) {
                return $query->where('user_id', Auth::id());
            })
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:Sales,Finance,Operations,HR,Other',
            'status' => 'required|in:Draft,Published,Archived',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        $filePath = null;
        if ($request->hasFile('report_file')) {
            $filePath = $request->file('report_file')->store('reports');
        }

        Report::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'file_path' => $filePath,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Report created successfully.');
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);
        return view('reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        $this->authorize('update', $report);
        return view('reports.edit', compact('report'));
    }
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:Sales,Finance,Operations,HR,Other',
            'status' => 'required|in:Draft,Published,Archived',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:2048',
        ]);

        $filePath = $report->file_path;
        if ($request->hasFile('report_file')) {
            if ($filePath) {
                Storage::delete($filePath);
            }
            $filePath = $request->file('report_file')->store('reports');
        }

        $report->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'status' => $validated['status'],
            'file_path' => $filePath,
        ]);

        return redirect()->route('reports.index')
            ->with('success', 'Report updated successfully.');
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        if ($report->file_path) {
            Storage::delete($report->file_path);
        }

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully.');
    }
    public function download(Report $report)
    {
        $this->authorize('view', $report);

        if (!$report->file_path) {
            abort(404);
        }

        return Storage::download($report->file_path);
    }
}
