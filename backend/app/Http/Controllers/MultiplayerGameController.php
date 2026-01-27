<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\GameSession;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MultiplayerGameController extends Controller
{
    /**
     * Submit an answer in multiplayer game.
     */
    public function submitAnswer(Request $request, string $lobbyId): JsonResponse
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'nullable|exists:options,id',
            'answer_text' => 'nullable|string',
        ]);

        $lobby = Lobby::findOrFail($lobbyId);
        $gameSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($gameSession->status !== 'in_progress') {
            return response()->json([
                'message' => 'Game is not in progress.',
            ], 422);
        }

        $question = Question::with('options')->findOrFail($validated['question_id']);

        // Provjeri da li je već odgovoreno na ovo pitanje
        $answers = $gameSession->answers ?? [];
        if (isset($answers[$validated['question_id']])) {
            return response()->json([
                'message' => 'You have already answered this question.',
            ], 422);
        }

        // Provjeri točnost odgovora
        $isCorrect = false;
        $pointsEarned = 0;

        if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
            $selectedOption = Option::find($validated['option_id']);
            if ($selectedOption && $selectedOption->is_correct) {
                $isCorrect = true;
                $pointsEarned = $question->points;
            }
        } elseif ($question->type === 'short_answer') {
            $correctAnswer = $question->options()->where('is_correct', true)->first();
            if ($correctAnswer && strtolower(trim($validated['answer_text'])) === strtolower(trim($correctAnswer->text))) {
                $isCorrect = true;
                $pointsEarned = $question->points;
            }
        }

        // Ažuriraj odgovore
        $answers[$validated['question_id']] = [
            'option_id' => $validated['option_id'] ?? null,
            'answer_text' => $validated['answer_text'] ?? null,
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'answered_at' => now()->toDateTimeString(),
        ];

        $gameSession->update([
            'answers' => $answers,
            'score' => $gameSession->score + $pointsEarned,
            'current_question_index' => $gameSession->current_question_index + 1,
        ]);

        return response()->json([
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'game_session' => $gameSession->fresh(),
        ]);
    }

    /**
     * Get current game state and leaderboard.
     */
    public function getGameState(Request $request, string $lobbyId): JsonResponse
    {
        $lobby = Lobby::with(['quiz.questions.options', 'gameSessions.user'])
            ->findOrFail($lobbyId);

        $gameSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Izračunaj leaderboard
        $leaderboard = GameSession::where('lobby_id', $lobby->id)
            ->where('status', 'in_progress')
            ->with('user')
            ->orderBy('score', 'desc')
            ->orderBy('current_question_index', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'user_id' => $session->user_id,
                    'user_name' => $session->user->name,
                    'score' => $session->score,
                    'total_points' => $session->total_points,
                    'percentage' => $session->total_points > 0 
                        ? round(($session->score / $session->total_points) * 100, 2) 
                        : 0,
                    'current_question_index' => $session->current_question_index,
                    'answers_count' => count($session->answers ?? []),
                ];
            });

        return response()->json([
            'lobby' => $lobby,
            'game_session' => $gameSession,
            'leaderboard' => $leaderboard,
        ]);
    }

    /**
     * Submit final answers and complete game.
     */
    public function completeGame(Request $request, string $lobbyId): JsonResponse
    {
        $lobby = Lobby::findOrFail($lobbyId);
        $gameSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($gameSession->status !== 'in_progress') {
            return response()->json([
                'message' => 'Game is not in progress.',
            ], 422);
        }

        // Izračunaj finalni rezultat
        $percentage = $gameSession->total_points > 0 
            ? round(($gameSession->score / $gameSession->total_points) * 100, 2) 
            : 0;

        $gameSession->update([
            'status' => 'completed',
            'completed_at' => now(),
            'percentage' => $percentage,
        ]);

        // Provjeri da li su svi igrači završili
        $remainingSessions = GameSession::where('lobby_id', $lobby->id)
            ->where('status', 'in_progress')
            ->count();

        if ($remainingSessions === 0) {
            $lobby->update([
                'status' => 'completed',
                'ended_at' => now(),
            ]);
        }

        // Finalni leaderboard
        $leaderboard = GameSession::where('lobby_id', $lobby->id)
            ->with('user')
            ->orderBy('score', 'desc')
            ->orderBy('completed_at', 'asc')
            ->get()
            ->map(function ($session) {
                return [
                    'user_id' => $session->user_id,
                    'user_name' => $session->user->name,
                    'score' => $session->score,
                    'total_points' => $session->total_points,
                    'percentage' => $session->percentage ?? 0,
                    'completed_at' => $session->completed_at,
                ];
            });

        return response()->json([
            'message' => 'Game completed successfully',
            'game_session' => $gameSession->fresh(),
            'leaderboard' => $leaderboard,
            'lobby_completed' => $lobby->status === 'completed',
        ]);
    }
}
