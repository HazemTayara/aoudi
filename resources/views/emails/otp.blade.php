{{-- resources/views/emails/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شحن العودة - إعادة تعيين كلمة المرور</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'Cairo', 'Tahoma', 'Arial', sans-serif;
            background: linear-gradient(135deg, #f9fafc 0%, #f0f2f5 100%);
            padding: 40px 20px;
            direction: rtl;
        }

        .email-container {
            max-width: 550px;
            margin: 0 auto;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header with logo */
        .email-header {
            background: linear-gradient(135deg, #e5a500 0%, #F6BE00 100%);
            text-align: center;
            padding: 40px 20px 30px;
            position: relative;
            overflow: hidden;
        }

        .email-header::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            right: -100px;
        }

        .email-header::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            bottom: -75px;
            left: -75px;
        }

        .logo-wrapper {
            display: inline-block;
            background: white;
            padding: 15px;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .logo-wrapper img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            display: block;
        }

        .email-header h1 {
            color: white;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        /* Content area */
        .email-content {
            padding: 40px 35px;
            background: white;
        }

        .greeting {
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            border-right: 4px solid #F6BE00;
            padding-right: 15px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        .message-text {
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            font-size: 16px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        /* OTP Card - FIXED for better display */
        .otp-card {
            background: linear-gradient(135deg, #fff9e6 0%, #fff4d4 100%);
            border: 2px solid #F6BE00;
            border-radius: 20px;
            padding: 25px 20px;
            margin: 30px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .otp-card::before {
            content: '🔐';
            position: absolute;
            font-size: 80px;
            opacity: 0.05;
            bottom: -20px;
            left: -20px;
            transform: rotate(-15deg);
        }

        .otp-label {
            font-size: 14px;
            color: #e5a500;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            margin-bottom: 15px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        /* Fixed OTP code display - smaller and fully visible */
        .otp-code-wrapper {
            background: white;
            border-radius: 15px;
            padding: 12px 20px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin: 10px 0;
            overflow-x: auto;
            max-width: 100%;
        }

        .otp-code {
            font-size: 28px;
            font-weight: 800;
            color: #e5a500;
            letter-spacing: 4px;
            font-family: 'Courier New', 'Monaco', monospace;
            direction: ltr;
            display: inline-block;
            word-break: keep-all;
            white-space: nowrap;
        }

        /* Responsive OTP code for mobile */
        @media (max-width: 500px) {
            .otp-code {
                font-size: 22px;
                letter-spacing: 3px;
            }

            .otp-code-wrapper {
                padding: 8px 15px;
            }
        }

        @media (max-width: 400px) {
            .otp-code {
                font-size: 18px;
                letter-spacing: 2px;
            }
        }

        .expiry-warning {
            margin-top: 15px;
            color: #ff9800;
            font-size: 13px;
            font-weight: 500;
        }

        /* Info boxes */
        .info-box {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 15px 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: #F6BE00;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .info-text {
            flex: 1;
        }

        .info-text strong {
            color: #e5a500;
            display: block;
            margin-bottom: 5px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        .info-text p {
            margin: 0;
            color: #666;
            font-size: 14px;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        .warning-box {
            background: #fff3e0;
            border-right: 4px solid #ff9800;
            padding: 15px 20px;
            border-radius: 12px;
            margin: 25px 0;
        }

        .warning-box i {
            color: #ff9800;
            margin-left: 10px;
        }

        /* Button */
        .btn-reset {
            display: inline-block;
            background: linear-gradient(135deg, #e5a500 0%, #F6BE00 100%);
            color: white !important;
            text-decoration: none;
            padding: 14px 35px;
            border-radius: 12px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(246, 190, 0, 0.3);
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(246, 190, 0, 0.4);
        }

        /* Footer */
        .email-footer {
            background: #f8f9fa;
            padding: 25px 35px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .email-footer p {
            color: #888;
            font-size: 13px;
            margin: 5px 0;
            font-family: 'Tajawal', 'Cairo', sans-serif;
        }

        hr {
            border: none;
            border-top: 1px solid #e9ecef;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header with Logo -->
        <div class="email-header">
            <div class="logo-wrapper">
                <img src="{{ asset('images/logo.png') }}" alt="شحن العودة">
            </div>
            <h1>إعادة تعيين كلمة المرور</h1>
        </div>

        <!-- Main Content -->
        <div class="email-content">
            <div class="greeting">
                مرحباً {{ $name }}،
            </div>

            <div class="message-text">
                لقد تلقينا طلباً لإعادة تعيين كلمة المرور لحسابك في <strong>شحن العودة</strong>.
                استخدم رمز التحقق أدناه لإكمال العملية.
            </div>

            <!-- OTP Card - FIXED VERSION -->
            <div class="otp-card">
                <div class="otp-label">
                    🔐 رمز التحقق الخاص بك
                </div>
                <div class="otp-code-wrapper">
                    <div class="otp-code">
                        {{ $otp }}
                    </div>
                </div>
                <div class="expiry-warning">
                    ⏰ سينتهي صلاحية هذا الرمز بعد 10 دقائق
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <div class="info-icon">
                    <span>💡</span>
                </div>
                <div class="info-text">
                    <strong>لماذا تلقيت هذا البريد؟</strong>
                    <p>تلقيت هذا البريد الإلكتروني لأن شخصاً ما (أو أنت) طلب إعادة تعيين كلمة المرور لحسابك في شحن
                        العودة.</p>
                </div>
            </div>

            <hr>

            <div class="warning-box" style="background: #fef2f2; border-right-color: #ef4444;">
                <i>⚠️</i>
                <strong style="color: #ef4444;">لم تطلب هذا؟</strong>
                <p style="margin-top: 8px;">إذا لم تطلب إعادة تعيين كلمة المرور، يرجى تجاهل هذا البريد الإلكتروني. لن
                    يتم تغيير كلمة المرور الخاصة بك.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>© {{ date('Y') }} شحن العودة. جميع الحقوق محفوظة</p>
            <p>هذا بريد إلكتروني آلي، يرجى عدم الرد عليه</p>
        </div>
    </div>
</body>

</html>