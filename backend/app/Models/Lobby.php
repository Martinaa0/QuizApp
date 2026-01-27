<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lobby extends Model
{
    protected $fillable = [
        'quiz_id',
        'host_id',
        'code',
        'status',
        'max_players',
        'current_players',
        'settings',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'max_players' => 'integer',
            'current_players' => 'integer',
        ];
    }

    /**
     * Get the quiz for this lobby.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the host user.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get all game sessions in this lobby.
     */
    public function gameSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * Get all players in this lobby.
     */
    public function players()
    {
        return $this->hasManyThrough(User::class, GameSession::class, 'lobby_id', 'id', 'id', 'user_id');
    }

    /**
     * Generate a unique lobby code.
     */
    public static function generateCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
