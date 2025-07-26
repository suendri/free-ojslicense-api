<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{

    public function check(Request $request)
    {
        $licenseKey = $request->get('key');
        $domain     = $request->get('domain');

        $license = License::where('license_key', $licenseKey)
            ->where('is_sold', 1)
            ->first();

        if (!$license) {
            return response()->json([
                'status' => 'error',
                'message' => 'License key not found or not purchased.'
            ]);
        }

        if ($license->is_active && $license->domain === $domain) {
            return response()->json([
                'status' => 'valid',
                'message' => 'License already activated for this domain.'
            ]);
        }

        if ($license->is_active && $license->domain !== $domain) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'License already used for another domain.'
            ]);
        }

        // Belum aktif, siap diaktivasi
        if (!$license->is_active && !$license->domain) {
            return response()->json([
                'status' => 'ready',
                'message' => 'License is valid and can be activated.'
            ]);
        }

        // Fallback
        return response()->json([
            'status' => 'error',
            'message' => 'License status not recognized.'
        ]);
    }

    public function activate(Request $request)
    {
        $licenseKey = $request->input('key');
        $domain     = $request->input('domain');
        $userAgent  = $request->header('User-Agent');
        $ipAddress  = $request->ip();

        if (!$licenseKey || !$domain) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing license key or domain.'
            ], 400);
        }

        $license = \App\Models\License::where('license_key', $licenseKey)->first();

        if (!$license) {
            return response()->json(['status' => 'invalid', 'message' => 'License not found.'], 404);
        }

        if (!$license->is_sold) {
            return response()->json(['status' => 'invalid', 'message' => 'License not yet sold.'], 403);
        }

        if ($license->is_active && $license->domain !== $domain) {
            return response()->json([
                'status' => 'invalid',
                'message' => 'License already activated for another domain.'
            ], 403);
        }

        if ($license->is_active && $license->domain === $domain) {
            return response()->json([
                'status' => 'activated',
                'message' => 'License already activated for this domain.'
            ]);
        }

        $license->domain = $domain;
        $license->is_active = 1;
        $license->activated_at = now();
        $license->save();

        // Log aktivitas
        DB::table('license_logs')->insert([
            'license_key' => $licenseKey,
            'domain' => $domain,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'action' => 'activate',
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'activated',
            'message' => 'License activated successfully.'
        ]);
    }
}
