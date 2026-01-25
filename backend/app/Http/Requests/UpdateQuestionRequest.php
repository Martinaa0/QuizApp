<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quiz_id' => 'sometimes|required|exists:quizzes,id',
            'text' => 'sometimes|required|string',
            'type' => 'sometimes|required|in:multiple_choice,true_false,short_answer',
            'points' => 'nullable|integer|min:1',
            'order' => 'nullable|integer|min:0',
        ];
    }
}
