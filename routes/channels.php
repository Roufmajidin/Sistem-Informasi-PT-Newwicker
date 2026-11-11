
<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// ============================================
// 1. CHANNEL BAWAAN LARAVEL
// ============================================
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ============================================
// 2. QC NOTIFICATION
// ============================================
Broadcast::channel('qc-activity', function ($user) {
    return $user !== null;
});

// ============================================
// 3. SOURCING MONITORING
// ============================================
Broadcast::channel('sm', function ($user) {

    $email = strtolower(trim((string) ($user->email ?? '')));

    $isFactory = Str::startsWith($email, 'factory@');

    $hasAccess = $user->accessModules()
        ->where('name', 'Sourcing Monitoring')
        ->exists();

    return $isFactory || $hasAccess;
});

// ============================================
// 4. PENGAJUAN HARGA NOTIFICATION
// ============================================
Broadcast::channel('pengajuan-harga.user.{id}', function ($user, $id) {

    $sameUser = (int) $user->id === (int) $id;

    $hasAccess = $user->accessModules()
        ->where('name', 'Pengajuan Harga')
        ->exists();

    Log::info('[PUSHER] Pengajuan Harga Auth', [
        'user_id' => $user->id,
        'channel_user_id' => $id,
        'same_user' => $sameUser,
        'has_access' => $hasAccess,
        'authorized' => $sameUser && $hasAccess,
    ]);

    return $sameUser && $hasAccess;
});
