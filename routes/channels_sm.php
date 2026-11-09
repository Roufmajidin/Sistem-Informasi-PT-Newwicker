<?php
// Tambahkan ke routes/channels.php
use Illuminate\Support\Facades\Broadcast;
Broadcast::channel('sm',function($user){
 return $user->accessModules()->where('name','Sourcing Monitoring')->exists()
     || $user->can('manage-sm-director');
});
// Pastikan auth endpoint broadcasting terdaftar (Laravel 12 bootstrap/app.php -> withBroadcasting(...))
