<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\GameSession;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LobbyController extends Controller
{
    /**
     * Create a new lobby.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'max_players' => 'nullable|integer|min:2|max:20',
            'settings' => 'nullable|array',
        ]);

        $quiz = Quiz::findOrFail($validated['quiz_id']);

        // Provjeri da li kviz ima pitanja
        if ($quiz->questions()->count() === 0) {
            return response()->json([
                'message' => 'Quiz must have at least one question.',
            ], 422);
        }

        // Kreiraj lobby
        $lobby = Lobby::create([
            'quiz_id' => $quiz->id,
            'host_id' => $request->user()->id,
            'code' => Lobby::generateCode(),
            'status' => 'waiting',
            'max_players' => $validated['max_players'] ?? 10,
            'current_players' => 1,
            'settings' => $validated['settings'] ?? [],
        ]);

        // Kreiraj game session za host-a
        GameSession::create([
            'lobby_id' => $lobby->id,
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'status' => 'waiting',
            'total_points' => $quiz->questions()->sum('points'),
        ]);

        $lobby->load(['quiz', 'host', 'gameSessions.user']);

        return response()->json([
            'message' => 'Lobby created successfully',
            'lobby' => $lobby,
        ], 201);
    }

    /**
     * Join a lobby by code.
     */
    public function join(Request $request, string $code): JsonResponse
    {
        $lobby = Lobby::where('code', strtoupper($code))
            ->with(['quiz', 'host', 'gameSessions.user'])
            ->first();

        if (!$lobby) {
            return response()->json([
                'message' => 'Lobby not found.',
            ], 404);
        }

        if ($lobby->status !== 'waiting') {
            return response()->json([
                'message' => 'Lobby is not accepting new players.',
            ], 422);
        }

        if ($lobby->current_players >= $lobby->max_players) {
            return response()->json([
                'message' => 'Lobby is full.',
            ], 422);
        }

        // Provjeri da li korisnik već postoji u lobby-ju
        $existingSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingSession) {
            return response()->json([
                'message' => 'You are already in this lobby.',
                'lobby' => $lobby,
            ]);
        }

        // Dodaj korisnika u lobby
        GameSession::create([
            'lobby_id' => $lobby->id,
            'user_id' => $request->user()->id,
            'quiz_id' => $lobby->quiz_id,
            'status' => 'waiting',
            'total_points' => $lobby->quiz->questions()->sum('points'),
        ]);

        $lobby->increment('current_players');
        $lobby->load(['quiz', 'host', 'gameSessions.user']);

        return response()->json([
            'message' => 'Joined lobby successfully',
            'lobby' => $lobby,
        ]);
    }

    /**
     * Get lobby details.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $lobby = Lobby::with(['quiz', 'host', 'gameSessions.user'])
            ->findOrFail($id);

        // Provjeri da li je korisnik u lobby-ju
        $userSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userSession && $lobby->host_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You are not a member of this lobby.',
            ], 403);
        }

        return response()->json($lobby);
    }

    /**
     * Start the game (only host can start).
     */
    public function start(Request $request, string $id): JsonResponse
    {
        $lobby = Lobby::with(['quiz.questions', 'gameSessions'])->findOrFail($id);

        // Provjeri da li je korisnik host
        if ($lobby->host_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the host can start the game.',
            ], 403);
        }

        if ($lobby->status !== 'waiting') {
            return response()->json([
                'message' => 'Game has already started or ended.',
            ], 422);
        }

        if ($lobby->current_players < 2) {
            return response()->json([
                'message' => 'At least 2 players are required to start.',
            ], 422);
        }

        DB::transaction(function () use ($lobby) {
            // Ažuriraj status lobby-ja
            $lobby->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            // Ažuriraj sve game sessions
            $lobby->gameSessions()->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        });

        $lobby->load(['quiz', 'host', 'gameSessions.user']);

        return response()->json([
            'message' => 'Game started successfully',
            'lobby' => $lobby,
        ]);
    }

    /**
     * Leave lobby.
     */
    public function leave(Request $request, string $id): JsonResponse
    {
        $lobby = Lobby::findOrFail($id);
        $userSession = GameSession::where('lobby_id', $lobby->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$userSession) {
            return response()->json([
                'message' => 'You are not in this lobby.',
            ], 404);
        }

        // Ako je host, prebaci host na drugog igrača ili zatvori lobby
        if ($lobby->host_id === $request->user()->id) {
            $otherSession = GameSession::where('lobby_id', $lobby->id)
                ->where('user_id', '!=', $request->user()->id)
                ->first();

            if ($otherSession) {
                $lobby->update(['host_id' => $otherSession->user_id]);
            } else {
                // Nema drugih igrača, zatvori lobby
                $lobby->update(['status' => 'cancelled']);
            }
        }

        $userSession->delete();
        $lobby->decrement('current_players');

        return response()->json([
            'message' => 'Left lobby successfully',
        ]);
    }

    /**
     * Get active lobbies.
     */
    public function index(Request $request): JsonResponse
    {
        $lobbies = Lobby::with(['quiz', 'host'])
            ->where('status', 'waiting')
            ->where('current_players', '<', DB::raw('max_players'))
            ->latest()
            ->get();

        return response()->json($lobbies);
    }
}
