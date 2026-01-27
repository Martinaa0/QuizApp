<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameSession extends Model
{
    protected $fillable = [
        'lobby_id',
        'user_id',
        'quiz_id',
        'score',
        'total_points',
        'percentage',
        'answers',
        'current_question_index',
        'started_at',
        'completed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'score' => 'integer',
            'total_points' => 'integer',
            'percentage' => 'decimal:2',
            'current_question_index' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * Get the lobby for this session.
     */
    public function lobby(): BelongsTo
    {
        return $this->belongsTo(Lobby::class);
    }

    /**
     * Get the user for this session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the quiz for this session.
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
