<!-- resources/views/print.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Patient Number</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        @font-face {
    font-family: 'Didone';
    src: url({{ asset('assets/fonts/DidoneRoomNumbers.otf') }});
}
        .container {
            border: 1px solid #000;
            padding: 20px;
            display: inline-block;
            position: relative; /* For positioning the background */
            overflow: hidden; /* Clip the pseudo-elements */
        }
        h1 {
            font-size: 24px;
        }
        .details {
            margin-top: 20px;
            font-size: 18px;
        }
        .bigFont {
            font-size: 70px;
            font-family: 'Didone', sans-serif; /* Set the font for the number */
        }
        /* Stylish background lines */
        .container::before,
        .container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: repeating-linear-gradient(
                135deg,
                rgba(0, 0, 0, 0.1) 0,
                rgba(0, 0, 0, 0.1) 1px,
                transparent 1px,
                transparent 20px
            );
            z-index: 0; /* Behind the content */
        }
        .container > * {
            position: relative; /* Bring content above the background */
            z-index: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="details">
            <p>{{ $patient->name }} {{ $patient->last_name }}</p>
            <h1 class="bigFont">{{ $printedNumber->number }}</h1>
            <p>{{ \Carbon\Carbon::parse($printedNumber->date)->format('Y-m-d') }}</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
