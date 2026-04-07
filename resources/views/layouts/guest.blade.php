{{-- layouts/guest.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شحن العودة - @yield('title')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@0.4.0/dist/css/bootstrap-rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f9fafc 0%, #f9fafc 100%);
            font-family: 'Tajawal', 'Cairo', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background shapes */
        body::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -150px;
            right: -150px;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -100px;
            left: -100px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate(0, 0);
            }

            50% {
                transform: translate(30px, 30px);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 55px rgba(0, 0, 0, 0.15);
        }

        .logo-container {
            text-align: center;
            padding: 30px 0 20px 0;
            background: linear-gradient(135deg, #e5a500 0%, #F6BE00 100%);
            margin: -1px -1px 0 -1px;
            border-radius: 30px 30px 0 0;
        }

        .logo-wrapper {
            display: inline-block;
            background: white;
            padding: 20px;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        .logo-wrapper img {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .card-header-custom {
            text-align: center;
            padding: 30px 30px 10px 30px;
            background: transparent;
            border: none;
        }

        .card-header-custom h3 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #e5a500 0%, #F6BE00 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }

        .card-header-custom p {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }

        .card-body-custom {
            padding: 30px;
        }

        .form-floating-custom {
            position: relative;
            margin-bottom: 25px;
        }

        .form-floating-custom input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }


        .form-floating-custom input:focus {
            border-color: #F6BE00;
            outline: none;
            box-shadow: 0 0 0 4px rgba(246, 190, 0, 0.1);
        }

        .form-floating-custom label {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            transition: all 0.3s ease;
            pointer-events: none;
            font-size: 14px;
            background: transparent;
            padding: 0;
        }

        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            transition: color 0.3s ease;
        }

        .form-floating-custom input:focus~.form-icon {
            color: #F6BE00;
        }

        .btn-modern {
            background: linear-gradient(135deg, #e5a500 0%, #F6BE00 100%);
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(246, 190, 0, 0.3);
        }

        .btn-modern:active {
            transform: translateY(0);
        }

        .forgot-link {
            color: #F6BE00;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .forgot-link:hover {
            color: #e5a500;
            transform: translateX(-5px);
        }

        .alert-modern {
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
            color: #155724;
            padding: 12px 20px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .glass-card {
                margin: 20px;
            }

            .card-body-custom {
                padding: 20px;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="d-flex align-items-center min-vh-100">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('scripts')
</body>

</html>