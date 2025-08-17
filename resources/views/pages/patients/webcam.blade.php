<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ localize('global.patient_photo_capture') }} - {{ $patient->name }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/core.css')}}" class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{asset('assets/vendor/css/rtl/theme-default.css')}}" class="template-customizer-theme-css" />
    
    
    <!-- Icons -->
    <link rel="stylesheet" href="{{asset('assets/vendor/fonts/boxicons.css')}}" />
    
    <!-- Webcam JS -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/webcamjs/webcam.min.js') }}"></script>
    @vite('resources/js/app.js')
    <style>
        @font-face {
            font-family: "persian_font";
            src: url({{ asset('assets/fonts/mod_font.ttf') }});
        }
        
        body {
            font-family: persian_font, "Public Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .header p {
            font-size: 1.1rem;
            margin: 10px 0 0 0;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .patient-info {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            text-align: center;
        }
        
        .patient-info h3 {
            color: #495057;
            margin: 0 0 10px 0;
            font-size: 1.3rem;
        }
        
        .patient-info p {
            color: #6c757d;
            margin: 5px 0;
            font-size: 1rem;
        }
        
        .content {
            padding: 40px;
        }
        
        .camera-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }
        
        .camera-container {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .camera-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .camera-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .camera-title i {
            color: #667eea;
            font-size: 1.6rem;
        }
        
        #my_camera {
            width: 100%;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        #results {
            width: 100%;
            min-height: 300px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }
        
        #results img {
            max-width: 100%;
            max-height: 100%;
            border-radius: 8px;
        }
        
        .camera-controls {
            margin-top: 20px;
            text-align: center;
        }
        
        .btn-capture {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-capture:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        
        .btn-capture:active {
            transform: translateY(0);
        }
        
        .form-actions {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            margin: 0 10px;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.6);
        }
        
        .btn-back {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.6);
            color: white;
            text-decoration: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading i {
            font-size: 2rem;
            color: #667eea;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .status-message {
            text-align: center;
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 600;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .camera-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="bx bx-camera"></i> {{ localize('global.patient_photo_capture') }}</h1>
            <p>{{ localize('global.capture_patient_photo_description') }}</p>
        </div>
        
        <div class="patient-info">
            <h3><i class="bx bx-user"></i> {{ localize('global.patient_information') }}</h3>
            <p><strong>{{ localize('global.name') }}:</strong> {{ $patient->name }} {{ $patient->last_name }}</p>
            <p><strong>{{ localize('global.nid') }}:</strong> {{ $patient->nid }}</p>
            <p><strong>{{ localize('global.age') }}:</strong> {{ $patient->age }} {{ localize('global.years') }}</p>
        </div>
        
        <form method="POST" action="{{ route('patients.capture', ['id' => $patient->id]) }}" enctype="multipart/form-data">
            @csrf
            <div class="content">
                <div class="camera-section">
                    <div class="camera-container">
                        <div class="camera-title">
                            <i class="bx bx-camera"></i>
                            {{ localize('global.live_camera') }}
                        </div>
                        <div id="my_camera"></div>
                        <div class="camera-controls">
                            <button type="button" class="btn-capture" onclick="take_snapshot()">
                                <i class="bx bx-camera"></i> {{ localize('global.take_photo') }}
                            </button>
                        </div>
                    </div>
                    
                    <div class="camera-container">
                        <div class="camera-title">
                            <i class="bx bx-image"></i>
                            {{ localize('global.captured_photo') }}
                        </div>
                        <div id="results">
                            <div style="text-align: center; color: #6c757d;">
                                <i class="bx bx-image" style="font-size: 3rem; margin-bottom: 10px;"></i>
                                <p>{{ localize('global.captured_image_will_appear_here') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="image" class="image-tag">
            </div>
            
            <div class="form-actions">
                <a href="{{ route('patients.show', $patient->id) }}" class="btn-back">
                    <i class="bx bx-arrow-back"></i> {{ localize('global.back') }}
                </a>
                <button type="submit" class="btn-save" id="saveBtn">
                    <i class="bx bx-save"></i> {{ localize('global.save_photo') }}
                </button>
            </div>
        </form>
    </div>

    <script>
        // Initialize webcam
        Webcam.set({
            width: 400,
            height: 300,
            image_format: 'jpeg',
            jpeg_quality: 90,
            force_flash: false
        });

        Webcam.attach('#my_camera');

        function take_snapshot() {
            // Show loading
            document.getElementById('results').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem; color: #667eea;"></i><p>در حال گرفتن عکس...</p></div>';
            
            Webcam.snap(function(data_uri) {
                $(".image-tag").val(data_uri);
                document.getElementById('results').innerHTML = '<img src="' + data_uri + '" style="max-width: 100%; max-height: 100%; border-radius: 8px;"/>';
                
                // Show success message
                showMessage('عکس با موفقیت گرفته شد!', 'success');
            });
        }

        function showMessage(message, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `status-message status-${type}`;
            messageDiv.innerHTML = message;
            
            const content = document.querySelector('.content');
            content.insertBefore(messageDiv, content.firstChild);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }

        // Form submission handling
        document.querySelector('form').addEventListener('submit', function(e) {
            const imageTag = document.querySelector('.image-tag');
            if (!imageTag.value) {
                e.preventDefault();
                showMessage('لطفاً ابتدا عکس بگیرید!', 'error');
                return false;
            }
            
            // Show loading on save button
            const saveBtn = document.getElementById('saveBtn');
            saveBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> در حال ذخیره...';
            saveBtn.disabled = true;
        });

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to camera containers
            const containers = document.querySelectorAll('.camera-container');
            containers.forEach(container => {
                container.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                container.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>
