<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use CrudTrait;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Add fields as needed by your app.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'email',
        'tenant_id',
    ];
}
