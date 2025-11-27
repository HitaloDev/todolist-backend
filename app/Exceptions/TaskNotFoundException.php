<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TaskNotFoundException extends Exception
{
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Task não encontrada',
            'error' => $this->getMessage() ?: 'A task solicitada não existe no sistema',
        ], 404);
    }
}