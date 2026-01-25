<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Option::with(['question']);

        // Filter by question_id
        if ($request->has('question_id')) {
            $query->where('question_id', $request->question_id);
        }

        $options = $query->orderBy('order')->get();

        return response()->json($options);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOptionRequest $request): JsonResponse
    {
        $question = Question::with('quiz')->findOrFail($request->question_id);

        // Provjeri autorizaciju - samo creator kviza ili admin mogu dodati opcije
        if ($question->quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only add options to questions in your own quizzes.',
            ], 403);
        }

        $option = Option::create([
            'question_id' => $request->question_id,
            'text' => $request->text,
            'is_correct' => $request->is_correct ?? false,
            'order' => $request->order ?? 0,
        ]);

        $option->load('question');

        return response()->json([
            'message' => 'Option created successfully',
            'option' => $option,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $option = Option::with(['question'])
            ->findOrFail($id);

        return response()->json($option);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOptionRequest $request, string $id): JsonResponse
    {
        $option = Option::with(['question.quiz'])->findOrFail($id);

        // Provjeri autorizaciju
        if ($option->question->quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only update options in your own quizzes.',
            ], 403);
        }

        $option->update($request->validated());
        $option->load('question');

        return response()->json([
            'message' => 'Option updated successfully',
            'option' => $option,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $option = Option::with(['question.quiz'])->findOrFail($id);

        // Provjeri autorizaciju
        if ($option->question->quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only delete options from your own quizzes.',
            ], 403);
        }

        $option->delete();

        return response()->json([
            'message' => 'Option deleted successfully',
        ]);
    }
}
