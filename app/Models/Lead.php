<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = ['name','contact','stage','value','status','client_id'];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    // Lead belongs to a Client via leads.client_id
    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class);
    }

    // Normalize on write: Title Case stage, lowercase status
    public function setStageAttribute($value): void
    {
        $v = strtolower(trim((string) $value));
        $map = ['contact' => 'contacted', 'contract' => 'contracted']; // typo smoothing (optional)
        $this->attributes['stage'] = ucfirst($map[$v] ?? $v); // e.g., "Contacted", "Closed"
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = strtolower(trim((string) $value)); // "open" | "won" | "lost"
    }

    // Case-insensitive check so old rows still work
    public function shouldBecomeClient(): bool
    {
        return strtolower((string) $this->stage) === 'closed'
            && strtolower((string) $this->status) === 'won';
    }
}
