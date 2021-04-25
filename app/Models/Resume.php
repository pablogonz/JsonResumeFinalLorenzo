<?php

namespace App\Models;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Eloquent will be useing this model to link to database table
 * @method static create(array $array)
 * @method static where(string $string, array|Application|Request|string|null $request)
 */
class Resume extends Model
{
    use HasFactory;
    // The Allowed to be fillable in the database table
    protected $fillable=[
        'Email',
        'Resume'
    ];
    // We automatically cast the Resume to Array when getting the data from database Json column
    protected $casts = [
      'Resume' => 'array'
    ];
}
