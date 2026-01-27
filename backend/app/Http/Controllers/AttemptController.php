<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitAnswerRequest;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\UserAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttemptController extends Controller
{
    /**
     * Start a new quiz attempt.
     */
    public function start(Request $request, string $id): JsonResponse
    {
        $quiz = Quiz::with('questions')->findOrFail($id);

        // Provjeri da li korisnik već ima aktivan pokušaj
        $existingAttempt = QuizAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quiz->id)
            ->where('status', 'in_progress')
            ->first();

        if ($existingAttempt) {
            $existingAttempt->load(['quiz', 'userAnswers']);
            return response()->json([
                'message' => 'You already have an active attempt for this quiz.',
                'attempt' => $existingAttempt,
            ]);
        }

        // Kreiraj novi pokušaj
        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'score' => 0,
            'total_points' => $quiz->questions->sum('points'),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $attempt->load(['quiz', 'userAnswers']);

        return response()->json([
            'message' => 'Quiz attempt started successfully',
            'attempt' => $attempt,
        ], 201);
    }

    /**
     * Submit an answer for a question.
     */
    public function submitAnswer(SubmitAnswerRequest $request): JsonResponse
    {
        $attempt = QuizAttempt::findOrFail($request->attempt_id);
        $question = Question::with('options')->findOrFail($request->question_id);

        // Provjeri da li pokušaj pripada korisniku
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. This attempt does not belong to you.',
            ], 403);
        }

        // Provjeri da li je pokušaj još aktivan
        if ($attempt->status !== 'in_progress') {
            return response()->json([
                'message' => 'This attempt has already been completed.',
            ], 400);
        }

        // Pronađi točan odgovor
        $correctOption = $question->options->where('is_correct', true)->first();
        $isCorrect = false;
        $pointsEarned = 0;

        if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
            // Provjeri da li je odabrana opcija točna
            if ($request->option_id && $correctOption && $request->option_id == $correctOption->id) {
                $isCorrect = true;
                $pointsEarned = $question->points;
            }
        } elseif ($question->type === 'text' || $question->type === 'short_answer') {
            // Za short answer, provjeri tekstualni odgovor (možeš dodati fuzzy matching)
            if ($request->answer_text && $correctOption) {
                $isCorrect = strtolower(trim($request->answer_text)) === strtolower(trim($correctOption->text));
                if ($isCorrect) {
                    $pointsEarned = $question->points;
                }
            }
        }

        // Provjeri da li već postoji odgovor za ovo pitanje
        $existingAnswer = UserAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->first();

        if ($existingAnswer) {
            // Ažuriraj postojeći odgovor
            $existingAnswer->update([
                'option_id' => $request->option_id,
                'answer_text' => $request->answer_text,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        } else {
            // Kreiraj novi odgovor
            UserAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'option_id' => $request->option_id,
                'answer_text' => $request->answer_text,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        }

        // Ažuriraj score u pokušaju
        $attempt->score = UserAnswer::where('attempt_id', $attempt->id)
            ->sum('points_earned');
        $attempt->save();

        return response()->json([
            'message' => 'Answer submitted successfully',
            'is_correct' => $isCorrect,
            'points_earned' => $pointsEarned,
        ]);
    }

    /**
     * Submit and complete the quiz attempt.
     */
    public function submit(Request $request, string $id): JsonResponse
    {
        $attempt = QuizAttempt::with(['quiz.questions', 'userAnswers'])->findOrFail($id);

        // Provjeri autorizaciju
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Provjeri da li je već završen
        if ($attempt->status === 'completed') {
            return response()->json([
                'message' => 'This attempt has already been completed.',
                'attempt' => $attempt,
            ]);
        }

        // Izračunaj finalni score
        $totalPoints = $attempt->quiz->questions->sum('points');
        $earnedPoints = $attempt->userAnswers->sum('points_earned');
        $percentage = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        // Ažuriraj pokušaj
        $attempt->update([
            'score' => $earnedPoints,
            'total_points' => $totalPoints,
            'percentage' => round($percentage, 2),
            'completed_at' => now(),
            'status' => 'completed',
        ]);

        $attempt->load(['quiz', 'userAnswers.question', 'userAnswers.option']);

        return response()->json([
            'message' => 'Quiz submitted successfully',
            'attempt' => $attempt,
        ]);
    }

    /**
     * Get attempt results.
     */
    public function results(Request $request, string $id): JsonResponse
    {
        $attempt = QuizAttempt::with([
            'quiz',
            'userAnswers.question',
            'userAnswers.option',
            'userAnswers.question.options',
        ])->findOrFail($id);

        // Provjeri autorizaciju
        if ($attempt->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        }

        // Formatiraj podatke za frontend
        $questions = $attempt->quiz->questions->map(function ($question) use ($attempt) {
            $userAnswer = $attempt->userAnswers->where('question_id', $question->id)->first();
            
            return [
                'question_id' => $question->id,
                'question_text' => $question->text,
                'points' => $question->points,
                'points_earned' => $userAnswer ? $userAnswer->points_earned : 0,
                'user_answer_id' => $userAnswer ? $userAnswer->option_id : null,
                'options' => $question->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'text' => $option->text,
                        'is_correct' => $option->is_correct,
                    ];
                }),
            ];
        });

        return response()->json([
            'attempt' => $attempt,
            'questions' => $questions,
        ]);
    }

    /**
     * Get user's attempts for a quiz.
     */
    public function userAttempts(Request $request, string $quizId): JsonResponse
    {
        $attempts = QuizAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quizId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($attempts);
    }
}
