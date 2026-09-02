<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .panel { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #667eea; }
        .button { background: #667eea; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Your E-Ticket is Ready! 🎫</h1>
        </div>
        <div class="content">
            <p>Hello {{ $passenger->first_name }},</p>
            <p>Your e-ticket for flight <strong>{{ $eticket->flight_number }}</strong> on <strong>{{ $eticket->departure_date->format('d M Y') }}</strong> is now ready!</p>

            <div class="panel">
                <strong>Booking Details:</strong>
                <ul>
                    <li><strong>PNR Code:</strong> {{ $booking->pnr_code }}</li>
                    <li><strong>E-Ticket Number:</strong> {{ $eticket->eticket_number }}</li>
                    <li><strong>Passenger:</strong> {{ $eticket->passenger_name }}</li>
                    <li><strong>Seat:</strong> {{ $eticket->seat_number }}</li>
                    <li><strong>Departure:</strong> {{ $eticket->departure_time }} from CGK</li>
                </ul>
            </div>

            <strong>Important Reminders:</strong>
            <ul>
                <li>Please arrive 2 hours before departure</li>
                <li>Bring your valid identification</li>
                <li>Keep this e-ticket safe</li>
            </ul>

            <p>The PDF e-ticket is attached to this email. You can print it or show it on your mobile device at check-in.</p>

            <a href="http://localhost:8001/booking/{{ $booking->pnr_code }}" class="button">View Booking</a>

            <p>Thank you for choosing Jelajahin Airlines!</p>
            <p>Best regards,<br><strong>Jelajahin Airlines Team</strong></p>
        </div>
    </div>
</body>
</html>
