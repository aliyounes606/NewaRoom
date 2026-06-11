<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    /**
     * Upload and store a file attachment for an article.
     */
    public function store(Request $request, Article $article): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file'],
        ]);

        $file = $request->file('file');
        $path = $file->store('attachments', 'local');

        $attachment = $article->attachments()->create([
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size'      => $file->getSize(),
        ]);

        return response()->json($attachment, 201);
    }
}
