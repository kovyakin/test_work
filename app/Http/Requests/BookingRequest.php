<?php

namespace App\Http\Requests;

use App\Rules\startTimeAndEndTime;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'resource_id' => 'required|integer|exists:resources,id',
            'start_time' => 'required|date_format:d-m-Y H:i:s',
            'end_time' => [
                'required',
                'date_format:d-m-Y H:i:s',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) <= strtotime($this->start_time)) {
                        $fail('Data end incorrect');
                    }
                },
            ],
        ];
    }

    protected function passedValidation(): void
    {
        $this->replace(
            [
                'user_id' => $this->user_id,
                'resource_id' => $this->resource_id,
                'start_time' => Carbon::make($this->start_time)->format('Y-m-d H:i:s'),
                'end_time' => Carbon::make($this->end_time)->format('Y-m-d H:i:s'),
            ]
        );
    }
}
