<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminFlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flight_number'  => 'required|string|max:10',
            'route_id'       => 'required|uuid|exists:routes,id',
            'aircraft_id'    => 'required|uuid|exists:aircraft,id',
            'departure_date' => 'required|date',
            'departure_time' => 'required|string',
            'arrival_time'   => 'required|string',
            'base_price'     => 'required|numeric|min:0',
            'tax_surcharge'  => 'nullable|numeric|min:0',
            'fuel_surcharge' => 'nullable|numeric|min:0',
            'status'         => 'required|in:scheduled,boarding,in_flight,landed,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'flight_number.required' => 'Nomor penerbangan wajib diisi.',
            'route_id.required'      => 'Rute wajib dipilih.',
            'aircraft_id.required'   => 'Pesawat wajib dipilih.',
        ];
    }
}
