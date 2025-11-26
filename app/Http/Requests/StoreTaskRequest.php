<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', 'string', 'in:pending,in_progress,completed'],
            'priority' => ['sometimes', 'string', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório',
            'title.max' => 'O título deve ter no máximo 255 caracteres',
            'status.in' => 'Status inválido. Valores aceitos: pending, in_progress, completed',
            'priority.in' => 'Prioridade inválida. Valores aceitos: low, medium, high',
            'due_date.date' => 'Data inválida',
            'due_date.after_or_equal' => 'A data deve ser hoje ou no futuro',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'description' => 'descrição',
            'status' => 'status',
            'priority' => 'prioridade',
            'due_date' => 'data de vencimento',
        ];
    }
}