<?php

namespace App\Actions;

use BangNokia\Aghanim\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ExampleAction extends Action
{
    /**
     * Execute the action.
     *
     * @param string $message
     * @param int $count
     * @return array
     */
    public function execute(string $message, int $count = 1): array
    {
        // Validate the parameters
        $validated = $this->validate([
            'message' => $message,
            'count' => $count,
        ]);

        // Process the action
        $result = [];
        for ($i = 0; $i < $validated['count']; $i++) {
            $result[] = $validated['message'];
        }

        return [
            'result' => $result,
            'timestamp' => now(),
            'user' => Auth::user() ? Auth::user()->name : 'Guest',
        ];
    }

    /**
     * Get the validation rules for the action parameters.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'message' => 'required|string|min:3',
            'count' => 'integer|min:1|max:10',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'message.required' => 'A message is required',
            'message.min' => 'The message must be at least 3 characters',
            'count.min' => 'Count must be at least 1',
            'count.max' => 'Count cannot be more than 10',
        ];
    }
}
