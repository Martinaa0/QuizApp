<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    /**
     * Get pre-made quizzes from Open Trivia Database.
     * Vraća gotove kvizove organizirane po kategorijama.
     */
    public function premadeQuizzes(Request $request): JsonResponse
    {
        $category = $request->get('category');
        $difficulty = $request->get('difficulty');
        
        try {
            // Definiraj pre-made kvizove po kategorijama
            $premadeQuizzes = [
                [
                    'id' => 'trivia_general_10',
                    'title' => 'General Knowledge Quiz',
                    'description' => 'Test your general knowledge with 10 questions',
                    'category' => 'General Knowledge',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 9, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_science_10',
                    'title' => 'Science & Nature Quiz',
                    'description' => 'Challenge yourself with science questions',
                    'category' => 'Science & Nature',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 17, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_history_10',
                    'title' => 'History Quiz',
                    'description' => 'Test your knowledge of historical events',
                    'category' => 'History',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 23, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_geography_10',
                    'title' => 'Geography Quiz',
                    'description' => 'How well do you know the world?',
                    'category' => 'Geography',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 22, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_sports_10',
                    'title' => 'Sports Quiz',
                    'description' => 'Test your sports knowledge',
                    'category' => 'Sports',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 21, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_computers_10',
                    'title' => 'Computer Science Quiz',
                    'description' => 'Questions about computers and technology',
                    'category' => 'Science: Computers',
                    'difficulty' => 'medium',
                    'question_count' => 10,
                    'duration' => 10,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 18, 'amount' => 10, 'difficulty' => 'medium'],
                ],
                [
                    'id' => 'trivia_easy_general_15',
                    'title' => 'Easy General Knowledge',
                    'description' => 'Easy questions for beginners',
                    'category' => 'General Knowledge',
                    'difficulty' => 'easy',
                    'question_count' => 15,
                    'duration' => 15,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 9, 'amount' => 15, 'difficulty' => 'easy'],
                ],
                [
                    'id' => 'trivia_hard_general_10',
                    'title' => 'Hard General Knowledge',
                    'description' => 'Challenge yourself with difficult questions',
                    'category' => 'General Knowledge',
                    'difficulty' => 'hard',
                    'question_count' => 10,
                    'duration' => 15,
                    'is_external' => true,
                    'source' => 'opentdb',
                    'api_params' => ['category' => 9, 'amount' => 10, 'difficulty' => 'hard'],
                ],
            ];

            // Filter kvizove ako je potrebno
            $filtered = collect($premadeQuizzes);
            
            if ($category) {
                $filtered = $filtered->filter(function ($quiz) use ($category) {
                    return stripos($quiz['category'], $category) !== false;
                });
            }
            
            if ($difficulty) {
                $filtered = $filtered->filter(function ($quiz) use ($difficulty) {
                    return $quiz['difficulty'] === $difficulty;
                });
            }

            return response()->json([
                'success' => true,
                'quizzes' => $filtered->values()->all(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch premade quizzes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get questions for a specific premade quiz.
     */
    public function getPremadeQuizQuestions(Request $request, string $quizId): JsonResponse
    {
        try {
            // Pronađi kviz po ID-u
            $premadeQuizzes = $this->getPremadeQuizzesList();
            $quiz = collect($premadeQuizzes)->firstWhere('id', $quizId);
            
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found',
                ], 404);
            }

            // Dohvati pitanja iz API-ja
            $url = 'https://opentdb.com/api.php';
            $params = array_merge($quiz['api_params'], ['type' => 'multiple']);
            
            $response = Http::timeout(10)->get($url, $params);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['response_code']) && $data['response_code'] === 0) {
                    // Formatiraj pitanja
                    $questions = collect($data['results'])->map(function ($q, $index) {
                        $options = array_merge($q['incorrect_answers'], [$q['correct_answer']]);
                        shuffle($options);
                        
                        return [
                            'id' => 'external_' . $index,
                            'text' => html_entity_decode($q['question'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                            'type' => 'multiple_choice',
                            'points' => $q['difficulty'] === 'easy' ? 1 : ($q['difficulty'] === 'medium' ? 2 : 3),
                            'order' => $index,
                            'options' => collect($options)->map(function ($opt, $optIndex) use ($q) {
                                return [
                                    'id' => 'external_opt_' . $optIndex,
                                    'text' => html_entity_decode($opt, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                                    'is_correct' => $opt === $q['correct_answer'],
                                    'order' => $optIndex,
                                ];
                            })->all(),
                        ];
                    })->all();

                    return response()->json([
                        'success' => true,
                        'quiz' => array_merge($quiz, [
                            'questions' => $questions,
                            'creator' => ['name' => 'Open Trivia Database', 'id' => 0],
                        ]),
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch questions',
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch quiz questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper method to get premade quizzes list.
     */
    private function getPremadeQuizzesList(): array
    {
        return [
            [
                'id' => 'trivia_general_10',
                'title' => 'General Knowledge Quiz',
                'description' => 'Test your general knowledge with 10 questions',
                'category' => 'General Knowledge',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 9, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_science_10',
                'title' => 'Science & Nature Quiz',
                'description' => 'Challenge yourself with science questions',
                'category' => 'Science & Nature',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 17, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_history_10',
                'title' => 'History Quiz',
                'description' => 'Test your knowledge of historical events',
                'category' => 'History',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 23, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_geography_10',
                'title' => 'Geography Quiz',
                'description' => 'How well do you know the world?',
                'category' => 'Geography',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 22, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_sports_10',
                'title' => 'Sports Quiz',
                'description' => 'Test your sports knowledge',
                'category' => 'Sports',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 21, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_computers_10',
                'title' => 'Computer Science Quiz',
                'description' => 'Questions about computers and technology',
                'category' => 'Science: Computers',
                'difficulty' => 'medium',
                'question_count' => 10,
                'duration' => 10,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 18, 'amount' => 10, 'difficulty' => 'medium'],
            ],
            [
                'id' => 'trivia_easy_general_15',
                'title' => 'Easy General Knowledge',
                'description' => 'Easy questions for beginners',
                'category' => 'General Knowledge',
                'difficulty' => 'easy',
                'question_count' => 15,
                'duration' => 15,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 9, 'amount' => 15, 'difficulty' => 'easy'],
            ],
            [
                'id' => 'trivia_hard_general_10',
                'title' => 'Hard General Knowledge',
                'description' => 'Challenge yourself with difficult questions',
                'category' => 'General Knowledge',
                'difficulty' => 'hard',
                'question_count' => 10,
                'duration' => 15,
                'is_external' => true,
                'source' => 'opentdb',
                'api_params' => ['category' => 9, 'amount' => 10, 'difficulty' => 'hard'],
            ],
        ];
    }

}
