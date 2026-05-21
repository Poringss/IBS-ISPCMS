<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = ['name','email','budget'];
    protected $casts = ['budget' => 'decimal:2'];

    public function leads()
    {
        return $this->hasMany(\App\Models\Lead::class);
    }
}
