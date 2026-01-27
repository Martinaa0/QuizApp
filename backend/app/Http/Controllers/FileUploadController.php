<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    /**
     * Upload an image file.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('quizzes', $fileName, 'public');

            return response()->json([
                'message' => 'File uploaded successfully',
                'path' => $filePath,
                'url' => Storage::disk('public')->url($filePath),
            ], 201);
        }

        return response()->json([
            'message' => 'No file provided',
        ], 400);
    }

    /**
     * Upload a PDF file.
     */
    public function uploadPdf(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:5120', // 5MB max
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            return response()->json([
                'message' => 'PDF uploaded successfully',
                'path' => $filePath,
                'url' => Storage::disk('public')->url($filePath),
            ], 201);
        }

        return response()->json([
            'message' => 'No file provided',
        ], 400);
    }
}
