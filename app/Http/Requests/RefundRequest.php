<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'uuid', 'exists:bookings,id'],
            'reason' => ['required', 'string', 'max:1000'],
            'refund_type' => ['required', 'in:full,partial'],
            'requested_amount' => ['required_if:refund_type,partial', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'booking_id.required' => 'Booking ID wajib diisi.',
            'booking_id.exists' => 'Booking tidak ditemukan.',
            'reason.required' => 'Alasan refund wajib diisi.',
            'reason.max' => 'Alasan refund maksimal 1000 karakter.',
            'refund_type.required' => 'Tipe refund wajib diisi.',
            'refund_type.in' => 'Tipe refund harus full atau partial.',
            'requested_amount.required_if' => 'Jumlah refund wajib diisi untuk tipe partial.',
            'requested_amount.min' => 'Jumlah refund harus lebih dari 0.',
        ];
    }
}
