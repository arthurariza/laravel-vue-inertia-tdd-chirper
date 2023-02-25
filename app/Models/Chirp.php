<?php

namespace App\Models;

use App\Events\ChirpCreated;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Chirp extends Model
{
    use HasFactory;

    protected $fillable = ['message'];

    protected $dispatchesEvents = [
        'created' => ChirpCreated::class
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => (new Carbon($value))->diffForHumans(),
        );
    }
}
