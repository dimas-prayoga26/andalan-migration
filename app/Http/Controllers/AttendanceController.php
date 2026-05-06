<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AttendanceController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();
        $authenticatedUser = Auth::user();
        $publicIp = '-';
        $attendance = Attendance::with('user')->where('user_id', $userId)->get();
        $showCompanyFilter = $this->isSuperUser($authenticatedUser);
        $companies = collect();

        if ($showCompanyFilter) {
            $companies = User::query()
                ->with([
                    'userEmployee.company:id,name',
                ])
                ->get()
                ->pluck('userEmployee.company')
                ->filter()
                ->unique('id')
                ->sortBy('name')
                ->values();
        }
        $izin = collect();
        $lembur = collect();
        $absensiHariIni = Attendance::where('date', now()->format('Y-m-d'))->where('user_id', $userId)->first();
        $totalLemburJam = 0;

        $agEvent = $attendance->groupBy(function ($data) {
            return Carbon::parse($data->date)->format('Y-m-d');
        })->map(function ($items) {
            return $items->map(function ($data) {
                return [
                    'check_in' => $data->check_in?->format('H:i'),
                    'check_out' => $data->check_out?->format('H:i'),
                    'status' => $data->status,
                ];
            })->values();
        });

        $officeLocation = $this->resolveOfficeContext($userId);

        $ipdataData = $this->fetchIpdata();
        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return view('absensi.index', compact(
            'attendance',
            'izin',
            'lembur',
            'agEvent',
            'totalLemburJam',
            'absensiHariIni',
            'officeLocation',
            'publicIp',
            'publicIpPrefix',
            'allowedIpPrefix',
            'isIpPrefixMatch',
            'companies',
            'showCompanyFilter',
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $nowJakarta = now('Asia/Jakarta');
        $todayDate = $nowJakarta->toDateString();
        $currentTime = $nowJakarta->format('H:i:s');
        $attendanceStatus = $nowJakarta->gt($nowJakarta->copy()->setTime(8, 0, 0)) ? 'late' : 'present';

        if (Attendance::where('user_id', $userId)
            ->whereDate('date', $todayDate)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah absen hari ini',
            ], 422);
        }

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $todayDate,
            'status' => $attendanceStatus,
        ]);

        $ipdataData = $this->fetchIpdata();
        $hasIpCoordinates = isset($ipdataData['latitude'], $ipdataData['longitude'])
            && is_numeric($ipdataData['latitude'])
            && is_numeric($ipdataData['longitude']);
        $latitude = $hasIpCoordinates ? (float) $ipdataData['latitude'] : 0.0;
        $longitude = $hasIpCoordinates ? (float) $ipdataData['longitude'] : 0.0;
        $ipAddress = $ipdataData['ip'] ?? $request->ip();

        $officeContext = $this->resolveOfficeContext($userId);
        $distance = 0.0;
        $radiusResult = 'outside';

        if ($officeContext !== null && $hasIpCoordinates) {
            $distance = $this->calculateDistanceInMeters(
                $latitude,
                $longitude,
                $officeContext['latitude'],
                $officeContext['longitude']
            );

            $radiusResult = $distance <= $officeContext['radius_meters'] ? 'inside' : 'outside';
        }

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'check_in' => $currentTime,
            'check_out' => null,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius_result' => $radiusResult,
            'distance' => round($distance, 2),
            'ip_address' => $ipAddress,
            'user_agent' => $request->userAgent(),
            'device_hash' => hash('sha256', ($request->userAgent() ?? 'unknown').'|'.$ipAddress),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen berhasil disimpan',
            'attendance_id' => $attendance->id,
        ]);
    }

    public function update(Request $request, Attendance $absensi): JsonResponse
    {
        if ($absensi->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke data absen ini',
            ], 403);
        }

        $todayDate = now('Asia/Jakarta')->toDateString();

        if ($absensi->date?->format('Y-m-d') !== $todayDate) {
            return response()->json([
                'success' => false,
                'message' => 'Data absen tidak sesuai tanggal hari ini',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Endpoint update belum digunakan',
        ]);
    }

    public function datatable(Request $request): JsonResponse
    {
        $authenticatedUser = Auth::user();
        if ($authenticatedUser instanceof User) {
            $authenticatedUser->loadMissing('userEmployee');
        }

        $isSuperUser = $this->isSuperUser($authenticatedUser);
        $isBoardOfDirectur = $this->isBoardOfDirectur($authenticatedUser);
        $userCompanyId = $authenticatedUser?->userEmployee?->company_id;
        $todayDate = now('Asia/Jakarta')->toDateString();
        $selectedCompanyId = $request->integer('company_id', 0);
        $tableUsersQuery = User::query()
            ->with([
                'userEmployee.company:id,name',
                'attendances' => function ($query) use ($todayDate): void {
                    $query->whereDate('date', $todayDate)->orderByDesc('id');
                },
            ]);

        if ($isBoardOfDirectur) {
            if ($userCompanyId) {
                $tableUsersQuery->whereHas('userEmployee', function ($query) use ($userCompanyId): void {
                    $query->where('company_id', $userCompanyId);
                });
            } else {
                $tableUsersQuery->whereRaw('1 = 0');
            }
        } elseif ($isSuperUser && $selectedCompanyId > 0) {
            $tableUsersQuery->whereHas('userEmployee', function ($query) use ($selectedCompanyId): void {
                $query->where('company_id', $selectedCompanyId);
            });
        }

        $tableRows = $tableUsersQuery->get()->map(function (User $user): array {
            $attendanceToday = $user->attendances->first();

            return [
                'staff_name' => $user->name,
                'company_name' => $user->userEmployee?->company?->name,
                'check_in' => $attendanceToday?->check_in?->format('H:i'),
                'check_out' => $attendanceToday?->check_out?->format('H:i'),
                'status' => $attendanceToday?->status,
            ];
        })->values();

        return response()->json([
            'data' => $tableRows,
        ]);
    }

    public function currentIp(): JsonResponse
    {
        $userId = Auth::id();
        $publicIp = '-';
        $officeLocation = $this->resolveOfficeContext($userId);
        $ipdataData = $this->fetchIpdata();

        if (! empty($ipdataData['ip'])) {
            $publicIp = (string) $ipdataData['ip'];
        }

        $allowedIpRange = is_array($officeLocation) ? ($officeLocation['ip_range'] ?? null) : null;
        $publicIpPrefix = $this->extractIpTwoOctets($publicIp);
        $allowedIpPrefix = $this->extractIpTwoOctets($allowedIpRange);
        $isIpPrefixMatch = $publicIpPrefix !== null
            && $allowedIpPrefix !== null
            && $publicIpPrefix === $allowedIpPrefix;

        return response()->json([
            'ip' => $publicIp,
            'public_ip_prefix' => $publicIpPrefix,
            'allowed_ip_prefix' => $allowedIpPrefix,
            'is_ip_prefix_match' => $isIpPrefixMatch,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchIpdata(): array
    {
        $ipdataApiKey = config('services.ipdata.api_key');

        if (empty($ipdataApiKey)) {
            return [];
        }

        try {
            $ipdataResponse = Http::timeout(7)
                ->acceptJson()
                ->get('https://api.ipdata.co', [
                    'api-key' => $ipdataApiKey,
                ]);

            if (! $ipdataResponse->successful()) {
                return [];
            }

            $ipdataData = $ipdataResponse->json();

            return is_array($ipdataData) ? $ipdataData : [];
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array{name: string|null, address: string|null, latitude: float, longitude: float, radius_meters: int, ip_range: string|null}|null
     */
    private function resolveOfficeContext(int $userId): ?array
    {
        $currentUser = User::query()
            ->with([
                'userEmployee.company:id,name,address,latitude,longitude',
            ])
            ->find($userId);

        $officeCompany = $currentUser?->userEmployee?->company;
        $companyId = $currentUser?->userEmployee?->company_id;

        if (! $officeCompany || $officeCompany->latitude === null || $officeCompany->longitude === null) {
            return null;
        }

        $attendanceRule = null;
        if ($companyId) {
            $attendanceRule = DB::table('rules_of_attendaces')
                ->where('companies_id', $companyId)
                ->where('is_active', true)
                ->orderByDesc('id')
                ->first(['radius', 'ip_range']);
        }

        return [
            'name' => $officeCompany->name,
            'address' => $officeCompany->address,
            'latitude' => (float) $officeCompany->latitude,
            'longitude' => (float) $officeCompany->longitude,
            'radius_meters' => (int) ($attendanceRule->radius ?? 10),
            'ip_range' => isset($attendanceRule?->ip_range) ? (string) $attendanceRule->ip_range : null,
        ];
    }

    private function extractIpTwoOctets(?string $ipValue): ?string
    {
        if ($ipValue === null) {
            return null;
        }

        $matches = [];
        if (preg_match('/(\d{1,3})\.(\d{1,3})/', $ipValue, $matches) !== 1) {
            return null;
        }

        $firstOctet = (int) $matches[1];
        $secondOctet = (int) $matches[2];

        if ($firstOctet > 255 || $secondOctet > 255) {
            return null;
        }

        return $firstOctet.'.'.$secondOctet;
    }

    private function calculateDistanceInMeters(float $startLat, float $startLng, float $endLat, float $endLng): float
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($endLat - $startLat);
        $longitudeDelta = deg2rad($endLng - $startLng);

        $a = sin($latitudeDelta / 2) * sin($latitudeDelta / 2)
            + cos(deg2rad($startLat)) * cos(deg2rad($endLat))
            * sin($longitudeDelta / 2) * sin($longitudeDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function isSuperUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)))
            ->contains('superuser');
    }

    private function isBoardOfDirectur(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $normalizedRoleNames = $user->getRoleNames()
            ->map(fn (string $roleName): string => strtolower(trim($roleName)));

        return $normalizedRoleNames->contains('board of directur')
            || $normalizedRoleNames->contains('board of directors');
    }
}
