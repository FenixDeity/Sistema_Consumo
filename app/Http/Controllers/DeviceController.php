<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\EnergyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function __construct(private EnergyService $energy) {}

    /** Dispositivos propios + los de las familias (grupos) del usuario. */
    public static function visibleQuery()
    {
        $user = Auth::user();
        $groupIds = $user->groupIds();

        return Device::query()
            ->with('group')
            ->where(function ($q) use ($user, $groupIds) {
                $q->where('user_id', $user->id);
                if ($groupIds) {
                    $q->orWhereIn('group_id', $groupIds);
                }
            })
            ->orderBy('name');
    }

    public function index()
    {
        $devices = self::visibleQuery()->get();
        $groups = Auth::user()->groups()->orderBy('name')->get();

        return view('devices.index', [
            'devices' => $devices,
            'groups' => $groups,
            'energy' => $this->energy,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = Auth::id();

        $device = new Device($data);
        $device->standby_watts = 0;
        $device->save();

        // El sistema calcula el consumo fantasma automáticamente
        $device->standby_watts = $this->energy->estimateStandbyWatts($device);
        $device->save();

        return redirect()->route('devices.index')->with('status', 'Dispositivo registrado.');
    }

    public function destroy(Device $device)
    {
        abort_unless($this->canManage($device), 403);
        $device->delete(); // borrado suave: los reportes conservan el historial

        return redirect()->route('devices.index')->with('status', 'Dispositivo eliminado (su historial se conserva en reportes).');
    }

    private function validated(Request $request): array
    {
        $groupIds = Auth::user()->groupIds();

        return $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'brand' => ['nullable', 'string', 'max:60'],
            'watts' => ['nullable', 'numeric', 'min:0', 'max:20000'],
            'volts' => ['nullable', 'numeric', 'min:0', 'max:600'],
            'amps' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'always_plugged' => ['nullable', 'boolean'],
            'group_id' => ['nullable', 'integer', 'in:'.implode(',', $groupIds ?: [0])],
            'notes' => ['nullable', 'string', 'max:300'],
        ]) + ['always_plugged' => $request->boolean('always_plugged')];
    }

    private function canManage(Device $device): bool
    {
        return $device->user_id === Auth::id()
            || ($device->group_id && in_array($device->group_id, Auth::user()->groupIds(), true));
    }
}
