<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        // Ako nema kvizova u bazi, vrati prazan array umjesto paginacije
        $quizzes = $query->latest()->get();
        $quizzesArray = $quizzes->toArray();

        // Dodaj premade kvizove iz API-ja ako nije specificiran filter
        if (!$request->has('search') && !$request->has('category') && !$request->has('difficulty')) {
            try {
                $apiController = new \App\Http\Controllers\ApiController();
                $premadeResponse = $apiController->premadeQuizzes($request);
                $premadeData = json_decode($premadeResponse->getContent(), true);
                
                if (isset($premadeData['quizzes']) && is_array($premadeData['quizzes'])) {
                    $premadeQuizzes = array_map(function ($quiz) {
                        return array_merge($quiz, [
                            'is_external' => true,
                            'questions' => [],
                            'questions_count' => $quiz['question_count'] ?? 0,
                            'creator' => ['name' => 'Open Trivia Database', 'id' => 0],
                        ]);
                    }, $premadeData['quizzes']);
                    
                    $quizzesArray = array_merge($quizzesArray, $premadeQuizzes);
                }
            } catch (\Exception $e) {
                // Ignore error, samo prikaži lokalne kvizove
                \Log::error('Error fetching premade quizzes: ' . $e->getMessage());
            }
        }

        return response()->json(array_values($quizzesArray));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreQuizRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('quizzes', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $quiz = Quiz::create($data);
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
        // Provjeri da li je external quiz
        if (str_starts_with($id, 'trivia_')) {
            try {
                $apiController = new \App\Http\Controllers\ApiController();
                $request = request();
                $response = $apiController->getPremadeQuizQuestions($request, $id);
                $data = json_decode($response->getContent(), true);
                if ($data['success'] && isset($data['quiz'])) {
                    return response()->json($data['quiz']);
                }
            } catch (\Exception $e) {
                // Fallback na 404
            }
            return response()->json(['message' => 'Quiz not found'], 404);
        }

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

        $data = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Obriši staru sliku ako postoji
            if ($quiz->image) {
                \Storage::disk('public')->delete($quiz->image);
            }

            $image = $request->file('image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('quizzes', $imageName, 'public');
            $data['image'] = $imagePath;
        }

        $quiz->update($data);
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
