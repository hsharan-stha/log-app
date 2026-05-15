<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'descriptor' => ['nullable', 'array', 'min:1'],
            'descriptor.*' => ['numeric'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $descriptor = $this->input('descriptor');

        if (is_string($descriptor) && $descriptor !== '') {
            $decoded = json_decode($descriptor, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->merge([
                    'descriptor' => Arr::wrap($decoded),
                ]);
            }
        }
    }
}
