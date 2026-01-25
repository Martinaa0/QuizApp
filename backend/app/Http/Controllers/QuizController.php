<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Quiz::with(['creator', 'questions']);

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by difficulty
        if ($request->has('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $quizzes = $query->latest()->paginate(15);

        return response()->json($quizzes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizRequest $request): JsonResponse
    {
        $quiz = Quiz::create([
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'image' => $request->image,
            'category' => $request->category,
            'difficulty' => $request->difficulty ?? 'medium',
            'is_active' => $request->is_active ?? true,
            'created_by' => $request->user()->id,
        ]);

        $quiz->load('creator');

        return response()->json([
            'message' => 'Quiz created successfully',
            'quiz' => $quiz,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $quiz = Quiz::with(['creator', 'questions.options'])
            ->findOrFail($id);

        return response()->json($quiz);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizRequest $request, string $id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        // Provjeri da li korisnik ima pravo ažurirati (samo creator ili admin)
        if ($quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own quizzes.',
            ], 403);
        }

        $quiz->update($request->validated());
        $quiz->load('creator');

        return response()->json([
            'message' => 'Quiz updated successfully',
            'quiz' => $quiz,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        // Provjeri da li korisnik ima pravo brisati (samo creator ili admin)
        if ($quiz->created_by !== $request->user()->id && $request->user()->user_type !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized. You can only delete your own quizzes.',
            ], 403);
        }

        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted successfully',
        ]);
    }
}
