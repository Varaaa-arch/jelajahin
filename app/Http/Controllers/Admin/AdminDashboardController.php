<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function __invoke(): Response
    {
        // TODO: ganti mock dengan query real Booking/Payment/Flight/User
        // saat tabel sudah terisi. Bentuk payload disamakan agar frontend
        // tidak perlu berubah.
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                [
                    'key' => 'revenue',
                    'label' => 'Total Revenue',
                    'value' => 'Rp 125.5M',
                    'trend' => '+12% from last month',
                    'trendTone' => 'up',
                    'icon' => 'Banknote',
                ],
                [
                    'key' => 'bookings',
                    'label' => 'Total Bookings',
                    'value' => '1,234',
                    'trend' => '+8% from last month',
                    'trendTone' => 'up',
                    'icon' => 'Ticket',
                ],
                [
                    'key' => 'pending',
                    'label' => 'Pending Orders',
                    'value' => '23',
                    'trend' => 'Awaiting payment',
                    'trendTone' => 'warn',
                    'icon' => 'Hourglass',
                ],
                [
                    'key' => 'occupancy',
                    'label' => 'Avg Occupancy',
                    'value' => '87%',
                    'trend' => 'Fleet utilization stable',
                    'trendTone' => 'neutral',
                    'icon' => 'Armchair',
                ],
                [
                    'key' => 'users',
                    'label' => 'Active Users',
                    'value' => '5.2K',
                    'trend' => '+15% from yesterday',
                    'trendTone' => 'up',
                    'icon' => 'UsersRound',
                ],
            ],
            'revenueTrend' => [
                'labels' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8', 'W9', 'W10', 'W11', 'W12'],
                'values' => [1.7, 2.4, 2.6, 1.9, 3.0, 2.8, 3.7, 3.5, 4.3, 4.9, 4.5, 4.6],
                'unit' => 'M',
            ],
            'bookingsByStatus' => [
                ['label' => 'Completed', 'value' => 60, 'color' => '#0d1117'],
                ['label' => 'Pending', 'value' => 25, 'color' => '#D1D5DB'],
                ['label' => 'Cancelled', 'value' => 15, 'color' => '#DC2626'],
            ],
            'topFlights' => [
                ['rank' => 1, 'route' => 'Jakarta → Bali', 'flightNo' => 'GA-101', 'bookings' => 142],
                ['rank' => 2, 'route' => 'Surabaya → Jakarta', 'flightNo' => 'GA-204', 'bookings' => 118],
                ['rank' => 3, 'route' => 'Medan → Jakarta', 'flightNo' => 'GA-305', 'bookings' => 98],
                ['rank' => 4, 'route' => 'Bali → Lombok', 'flightNo' => 'GA-411', 'bookings' => 87],
                ['rank' => 5, 'route' => 'Makassar → Jakarta', 'flightNo' => 'GA-550', 'bookings' => 76],
            ],
            'recentTransactions' => [
                ['id' => 'TRX-8921', 'pnr' => 'XYZ123', 'passenger' => 'Budi Santoso', 'amount' => 'Rp 2.5M'],
                ['id' => 'TRX-8920', 'pnr' => 'ABC987', 'passenger' => 'Siti Aminah', 'amount' => 'Rp 1.8M'],
                ['id' => 'TRX-8919', 'pnr' => 'KLM456', 'passenger' => 'John Doe', 'amount' => 'Rp 3.2M'],
                ['id' => 'TRX-8918', 'pnr' => 'DEF321', 'passenger' => 'Agus Pratama', 'amount' => 'Rp 950K'],
                ['id' => 'TRX-8917', 'pnr' => 'GHI654', 'passenger' => 'Rina Melati', 'amount' => 'Rp 1.1M'],
            ],
            'alerts' => [
                [
                    'key' => 'capacity',
                    'title' => 'Capacity Warning',
                    'message' => '5 flights departing today have occupancy below 40%.',
                    'tone' => 'amber',
                    'icon' => 'TriangleAlert',
                ],
                [
                    'key' => 'gateway',
                    'title' => 'Payment Gateway',
                    'message' => '3 consecutive payment failures reported on Gateway B.',
                    'tone' => 'red',
                    'icon' => 'CircleAlert',
                ],
                [
                    'key' => 'maintenance',
                    'title' => 'Scheduled Maintenance',
                    'message' => 'System database optimization scheduled for 02:00 AM.',
                    'tone' => 'blue',
                    'icon' => 'Info',
                ],
                [
                    'key' => 'api',
                    'title' => 'API Status',
                    'message' => 'All external partner APIs are operational and responding.',
                    'tone' => 'emerald',
                    'icon' => 'CircleCheck',
                ],
            ],
        ]);
    }
}
