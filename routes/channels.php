<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Str;

// Channel bawaan Laravel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel QC Notification
Broadcast::channel('qc-activity', function ($user) {
    return $user !== null;
});

// Channel Sourcing Monitoring
Broadcast::channel('sm', function ($user) {

    $email = strtolower(trim((string) $user->email));

    // Akun Factory sebagai Director
    $isFactory = Str::startsWith($email, 'factory@');

    // User dengan akses modul SM
    $hasAccess = $user->accessModules()
        ->where('name', 'Sourcing Monitoring')
        ->exists();

    return $isFactory || $hasAccess;
});
