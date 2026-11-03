<?php

use Illuminate\Support\Facades\Broadcast;

// Channel bawaan yang sudah ada
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Channel QC Notification
Broadcast::channel('qc-activity', function ($user) {
    return $user !== null;
});