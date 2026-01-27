<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Question::with(['quiz', 'options']);

        // Filter by quiz_id
        if ($request->has('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $questions = $query->orderBy('order')->get();

        return response()->json($questions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuestionRequest $request): JsonResponse
    {
        $quiz = Quiz::findOrFail($request->quiz_id);

        // Provjeri autorizaciju - samo creator kviza ili admin mogu dodati pitanja
        if ($quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only add questions to your own quizzes.',
            ], 403);
        }

        $question = Question::create([
            'quiz_id' => $request->quiz_id,
            'text' => $request->text,
            'type' => $request->type,
            'points' => $request->points ?? 1,
            'order' => $request->order ?? 0,
        ]);

        $question->load(['quiz', 'options']);

        return response()->json([
            'message' => 'Question created successfully',
            'question' => $question,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $question = Question::with(['quiz', 'options'])
            ->findOrFail($id);

        return response()->json($question);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuestionRequest $request, string $id): JsonResponse
    {
        $question = Question::with('quiz')->findOrFail($id);

        // Provjeri autorizaciju
        if ($question->quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only update questions in your own quizzes.',
            ], 403);
        }

        $question->update($request->validated());
        $question->load(['quiz', 'options']);

        return response()->json([
            'message' => 'Question updated successfully',
            'question' => $question,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $question = Question::with('quiz')->findOrFail($id);

        // Provjeri autorizaciju
        if ($question->quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only delete questions from your own quizzes.',
            ], 403);
        }

        $question->delete();

        return response()->json([
            'message' => 'Question deleted successfully',
        ]);
    }

    /**
     * Reorder questions for a quiz.
     */
    public function reorder(Request $request, string $id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        // Provjeri autorizaciju
        if ($quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only reorder questions in your own quizzes.',
            ], 403);
        }

        $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|exists:questions,id',
            'order.*.order' => 'required|integer|min:0',
        ]);

        foreach ($request->order as $item) {
            Question::where('id', $item['id'])
                ->where('quiz_id', $quiz->id)
                ->update(['order' => $item['order']]);
        }

        // Refresh quiz with updated order
        $quiz->load(['creator', 'questions' => function ($query) {
            $query->orderBy('order');
        }]);

        return response()->json([
            'message' => 'Questions reordered successfully',
            'quiz' => $quiz,
        ]);
    }
}
