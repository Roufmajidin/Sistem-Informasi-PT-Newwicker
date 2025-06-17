<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyyer extends Model
{
    //
    protected $table    = 'list_buyers';
    protected $fillable = ['name', 'status'];

}
