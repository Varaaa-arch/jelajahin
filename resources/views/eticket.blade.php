<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .ticket {
            max-width: 600px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .eticket-number {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 5px;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .field {
            flex: 1;
        }
        .label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .value {
            color: #333;
            font-size: 16px;
            margin-top: 5px;
        }
        .flight-info {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .route {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .airport {
            text-align: center;
        }
        .airport-code {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        .airport-time {
            font-size: 14px;
            margin-top: 5px;
        }
        .arrow {
            font-size: 20px;
            color: #ccc;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>Boarding Pass</h1>
            <div class="eticket-number">{{ $eticket_number }}</div>
        </div>

        <div class="content">
            <!-- Passenger Info -->
            <div class="section">
                <div class="section-title">PASSENGER</div>
                <div class="value">{{ $passenger_title }}. {{ $passenger_name }}</div>
            </div>

            <!-- PNR & Booking -->
            <div class="section">
                <div class="row">
                    <div class="field">
                        <div class="label">Booking Reference</div>
                        <div class="value">{{ $pnr_code }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Booking Date</div>
                        <div class="value">{{ $booking_date }}</div>
                    </div>
                </div>
            </div>

            <!-- Flight Info -->
            <div class="section">
                <div class="section-title">FLIGHT DETAILS</div>
                <div class="flight-info">
                    <div class="route">
                        <div class="airport">
                            <div class="airport-code">CGK</div>
                            <div class="airport-time">{{ $departure_time }}</div>
                            <div class="airport-time">{{ $departure_date }}</div>
                        </div>
                        <div class="arrow">✈</div>
                        <div class="airport">
                            <div class="airport-code">DPS</div>
                            <div class="airport-time">{{ $arrival_time }}</div>
                            <div class="airport-time">{{ $departure_date }}</div>
                        </div>
                    </div>
                    <div style="text-align: center; margin-top: 15px;">
                        <div class="label">Flight Number</div>
                        <div style="font-size: 18px; font-weight: bold; color: #333;">{{ $flight_number }}</div>
                    </div>
                </div>
            </div>

            <!-- Seat & Gate -->
            <div class="section">
                <div class="row">
                    <div class="field">
                        <div class="label">Seat</div>
                        <div class="value">{{ $seat_number }}</div>
                    </div>
                    <div class="field">
                        <div class="label">Gate</div>
                        <div class="value">{{ $gate }}</div>
                    </div>
                </div>
            </div>

            <!-- Important Info -->
            <div class="section" style="background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                <div class="label">⚠️ IMPORTANT</div>
                <div style="font-size: 12px; margin-top: 8px;">
                    Please arrive 2 hours before departure. Bring this e-ticket and valid identification.
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a valid travel document. Keep this e-ticket safe for your journey.</p>
            <p>Generated: {{ date('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
