<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class NewPengajuanController extends Controller
{
    //
    public function index(){
// dummy data orang
      $users = User::orderBy('name')->get();

        return view('pages.new_pengajuan.index', compact('users'));
    }
}
