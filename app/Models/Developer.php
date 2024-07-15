<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'date_of_birth'];
    protected $dates = ['date_of_birth'];
}
