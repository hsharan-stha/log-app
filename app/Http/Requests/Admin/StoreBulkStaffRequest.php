<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Support\BulkStaffParser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreBulkStaffRequest extends FormRequest
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
            'list' => ['required', 'string', 'max:100000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $text = Str::of($this->input('list', ''))->toString();
            $rows = BulkStaffParser::parse($text);

            if ($rows === []) {
                $validator->errors()->add('list', 'Enter at least one staff line.');

                return;
            }

            if (count($rows) > 500) {
                $validator->errors()->add('list', 'Maximum 500 staff lines per submission.');

                return;
            }

            $emailsInList = [];

            foreach ($rows as $row) {
                $n = $row['line'];
                $name = $row['name'];
                $email = $row['email'];

                if ($name === '') {
                    $validator->errors()->add('list', "Line {$n}: name is required.");

                    continue;
                }

                if (Str::length($name) > 255) {
                    $validator->errors()->add('list', "Line {$n}: name must be 255 characters or fewer.");

                    continue;
                }

                if ($email !== null) {
                    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $validator->errors()->add('list', "Line {$n}: invalid email address.");

                        continue;
                    }

                    if (isset($emailsInList[$email])) {
                        $validator->errors()->add('list', "Line {$n}: duplicate email in this list (also on line {$emailsInList[$email]}).");

                        continue;
                    }

                    $emailsInList[$email] = $n;

                    if (User::query()->where('email', $email)->exists()) {
                        $validator->errors()->add('list', "Line {$n}: that email is already used by another user.");
                    }
                }
            }
        });
    }
}
