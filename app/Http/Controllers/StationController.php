<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{

    public function index(Station $station)
    {
        $stations = Station::withCount('employees')->paginate(10);
        return view('stations.index', compact('stations'));
    }

    public function create()
    {
        return view('stations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'monthly_loss' => 'required|numeric',
            'deductions' => 'numeric',
        ]);

        Station::create($validated);

        return redirect()->route('stations.index')->with('success', 'Station created successfully!');
    }

    public function show(Station $station)
    {
        return view('stations.show', compact('station'));
    }

    public function edit(Station $station)
    {
        return view('stations.edit', compact('station'));
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'monthly_loss' => 'required|numeric',
            'deductions' => 'numeric',
        ]);

        $station->update($validated);

        return redirect()->route('stations.index')->with('success', 'Station updated successfully!');
    }

    public function destroy(Station $station)
    {
        $station->delete();
        return redirect()->route('stations.index')->with('success', 'Station deleted successfully!');
    }

}
