<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidTaskStatusException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Status inválido',
            'error' => $this->getMessage() ?: 'O status fornecido não é válido. Valores aceitos: pending, in_progress, completed',
        ], 400);
    }
}