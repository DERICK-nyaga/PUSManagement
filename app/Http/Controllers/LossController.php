<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loss;
use App\Models\Station;
use App\Models\Employee;
class LossController extends Controller
{
     public function index()
    {
        $losses = Loss::with(['station', 'employee'])
            ->orderBy('date_occurred', 'desc')
            ->paginate(20);

        return view('losses.index', compact('losses'));
    }

    public function stationLosses(Station $station)
    {
        $losses = $station->losses()
            ->with('employee')
            ->orderBy('date_occurred', 'desc')
            ->paginate(20);

        return view('losses.station', compact('losses', 'station'));
    }

    public function create()
    {
        $stations = Station::all();
        $employees = Employee::where('status', 'active')->get();

        return view('losses.create', compact('stations', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => 'required|exists:stations,id',
            'employee_id' => 'nullable|exists:employees,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:1000',
            'type' => 'required|in:cash,inventory,equipment,other',
            'date_occurred' => 'required|date',
        ]);

        Loss::create($validated);

        return redirect()->route('losses.index')
            ->with('success', 'Loss recorded successfully');
    }

    public function show(string $id)
    {

    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {

    }

    public function destroy(string $id)
    {

    }
}
