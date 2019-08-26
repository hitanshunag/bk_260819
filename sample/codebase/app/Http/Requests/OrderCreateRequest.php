<?php

namespace App\Http\Requests;

use App\Rules\OrderSchema;

class OrderCreateRequest extends AbstractFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            '*' => new OrderSchema,
            'origin'      => [
                'required',
                'array',
                function ($attr, $value, $fail) {
                    if (empty($value) || count($value) !== 2
                        || empty($value[0])
                        || empty($value[1])
                        || ! is_numeric($value[0])
                        || ! is_numeric($value[1])
                    ) {
                        $fail('INVALID_PARAMETERS');
                    }
                },
            ],
            'destination' => [
                'required',
                'array',
                function ($attr, $value, $fail) {
                    if (empty($value) || count($value) !== 2
                        || empty($value[0])
                        || empty($value[1])
                        || ! is_numeric($value[0])
                        || ! is_numeric($value[1])
                    ) {
                        $fail('INVALID_PARAMETERS');
                    }
                },
            ],
        ];
    }

    /**
     * Custom message for validation
     *
     * @return array
     */

    public function messages()
    {
        return [
            'origin.required'      => 'INVALID_PARAMETERS',
            'destination.required' => 'INVALID_PARAMETERS',
        ];
    }
}
