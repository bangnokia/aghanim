<?php

namespace BangNokia\Aghanim\Actions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

abstract class Action
{
    /**
     * Execute the action.
     *
     * @param mixed ...$params
     * @return mixed
     */
    abstract public function execute(...$params);

    /**
     * Get the validation rules for the action parameters.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Validate the given parameters against the rules.
     *
     * @param array $params
     * @return array
     * @throws ValidationException
     */
    protected function validate(array $params): array
    {
        $rules = $this->rules();
        
        if (empty($rules)) {
            return $params;
        }

        return Validator::make($params, $rules, $this->messages())->validate();
    }
}
