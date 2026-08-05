<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PowerOutage;
use App\Models\UsageLog;
use App\Services\EnergyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsumptionController extends Controller
{
    public function __construct(private EnergyService $energy) {}

    public function index(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
        $devices = DeviceController::visibleQuery()->get();

        $logs = UsageLog::with('device')
            ->whereIn('device_id', $devices->pluck('id'))
            ->whereDate('log_date', $date)
            ->latest('id')
            ->get();

        $openOutage = PowerOutage::where('user_id', Auth::id())->whereNull('ended_at')->latest('id')->first();
        $outages = PowerOutage::where('user_id', Auth::id())
            ->whereDate('started_at', $date)->latest('id')->get();

        return view('consumo.index', [
            'date' => $date,
            'devices' => $devices,
            'logs' => $logs,
            'openOutage' => $openOutage,
            'outages' => $outages,
            'energy' => $this->energy,
        ]);
    }

    public function store(Request $request)
    {
        $devices = DeviceController::visibleQuery()->pluck('id')->all();

        $data = $request->validate([
            'device_id' => ['required', 'integer', 'in:'.implode(',', $devices ?: [0])],
            'log_date' => ['required', 'date'],
            'mode' => ['required', 'in:time,cycles'],
            'minutes' => ['required_if:mode,time', 'nullable', 'integer', 'min:1'],
            'cycles' => ['required_if:mode,cycles', 'nullable', 'integer', 'min:1'],
            'cycle_minutes' => ['required_if:mode,cycles', 'nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:200'],
        ], [
            'minutes.required_if' => 'Indica los minutos de uso.',
            'cycles.required_if' => 'Indica cuántos ciclos usaste el dispositivo.',
            'cycle_minutes.required_if' => 'Indica cuánto duró cada ciclo.',
        ]);

        $device = Device::findOrFail($data['device_id']);

        if ($data['mode'] === 'time') {
            $data['cycles'] = null;
            $data['cycle_minutes'] = null;
        } else {
            $data['minutes'] = null;
        }

        $data['user_id'] = Auth::id();
        $data['kwh'] = $this->energy->computeKwh(
            $this->energy->deviceWatts($device),
            $data['mode'],
            $data['minutes'] ?? null,
            $data['cycles'] ?? null,
            $data['cycle_minutes'] ?? null
        );

        UsageLog::create($data);

        return back()->with('status', 'Consumo registrado.');
    }

    public function destroy(UsageLog $usageLog)
    {
        abort_unless($usageLog->user_id === Auth::id(), 403);
        $usageLog->delete();

        return back()->with('status', 'Registro eliminado.');
    }

    /** Interruptor de apagón: inicia si no hay uno abierto, cierra y calcula si ya existe. */
    public function toggleOutage(Request $request)
    {
        $open = PowerOutage::where('user_id', Auth::id())->whereNull('ended_at')->latest('id')->first();

        if ($open) {
            $open->ended_at = now();
            $open->minutes = max(1, $open->started_at->diffInMinutes($open->ended_at));
            $open->save();

            return back()->with('status', "Apagón cerrado: {$open->minutes} minutos sin luz.");
        }

        PowerOutage::create([
            'user_id' => Auth::id(),
            'started_at' => now(),
            'note' => $request->input('note'),
        ]);

        return back()->with('status', 'Apagón iniciado. Desactiva el interruptor cuando regrese la luz.');
    }

    /** Registro manual de un apagón con duración estimada. */
    public function storeOutage(Request $request)
    {
        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'minutes' => ['required', 'integer', 'min:1', 'max:20160'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        $start = Carbon::parse($data['started_at']);

        PowerOutage::create([
            'user_id' => Auth::id(),
            'started_at' => $start,
            'ended_at' => $start->copy()->addMinutes($data['minutes']),
            'minutes' => $data['minutes'],
            'note' => $data['note'] ?? null,
        ]);

        return back()->with('status', 'Apagón registrado.');
    }
}
