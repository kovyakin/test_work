<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResourcesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:100',
                Rule::unique('resources', 'name')->ignore($this->resource)
            ],
            'type' => [
                'required',
                'min:3',
                'max:50',
            ],
            'description' => [
                'sometimes',
                'min:3',
                'max:255',
            ],
        ];
    }
}
