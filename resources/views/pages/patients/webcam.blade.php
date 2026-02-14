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
        // Wait for webcam to be loaded
        function initializeWebcam() {
            // Check if Webcam is available
            if (typeof Webcam === 'undefined') {
                console.log('Waiting for Webcam library to load...');
                setTimeout(initializeWebcam, 100);
                return;
            }

            // Check if browser supports getUserMedia
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                var errorDiv = document.querySelector('#my_camera');
                if (errorDiv) {
                    errorDiv.innerHTML = `
                        <div style="background: #f8d7da; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; text-align: center;">
                            <h3 style="color: #721c24;">
                                <i class="bx bx-error-circle" style="font-size: 2rem;"></i><br>
                                مرورگر شما از دوربین پشتیبانی نمی‌کند
                            </h3>
                            <p style="color: #721c24;">
                                لطفاً از مرورگرهای مدرن مانند Chrome، Firefox، Edge یا Safari استفاده کنید.
                            </p>
                        </div>
                    `;
                }
                return;
            }

            // Wait for webcam to be ready
            if (!Webcam.loaded) {
                // Listen for load event if available
                if (typeof Webcam.on === 'function') {
                    Webcam.on('load', function() {
                        setupWebcam();
                    });
                }
                
                // Also check periodically as fallback
                var checkCount = 0;
                var checkInterval = setInterval(function() {
                    checkCount++;
                    if (Webcam.loaded) {
                        clearInterval(checkInterval);
                        setupWebcam();
                    } else if (checkCount >= 30) { // 3 seconds max wait
                        clearInterval(checkInterval);
                        console.warn('Webcam loading timeout, attempting to initialize anyway...');
                        setupWebcam();
                    }
                }, 100);
            } else {
                setupWebcam();
            }
        }

        // Function to retry webcam initialization
        function retryWebcam() {
            var errorDiv = document.querySelector('.webcam-permission-error');
            if (errorDiv) {
                errorDiv.remove();
            }
            
            // Reset webcam
            if (typeof Webcam !== 'undefined') {
                try {
                    Webcam.reset();
                } catch(e) {
                    console.log('Webcam reset:', e);
                }
            }
            
            // Reinitialize
            setTimeout(function() {
                initializeWebcam();
            }, 500);
        }

        function setupWebcam() {
            try {
                // Set up error handler before attaching
                if (typeof Webcam.on === 'function') {
                    Webcam.on('error', function(err) {
                        console.error('Webcam error:', err);
                        handleWebcamError(err);
                    });
                    
                    Webcam.on('live', function() {
                        console.log('Webcam is live');
                        // Hide any error messages when webcam becomes live
                        var errorMsg = document.querySelector('.webcam-permission-error');
                        if (errorMsg) {
                            errorMsg.remove();
                        }
                    });
                }

                // Initialize webcam
                Webcam.set({
                    width: 400,
                    height: 300,
                    image_format: 'jpeg',
                    jpeg_quality: 90,
                    force_flash: false
                });

                Webcam.attach('#my_camera');
                
                // Show success message
                console.log('Webcam initialized successfully');
            } catch (error) {
                console.error('Error initializing webcam:', error);
                handleWebcamError(error);
            }
        }

        function handleWebcamError(err) {
            var errorMessage = '';
            var errorName = err && err.name ? err.name : '';
            var errorMsg = err && err.message ? err.message : String(err);
            
            // Check for permission errors
            if (errorName === 'NotAllowedError' || errorMsg.includes('Permission denied') || errorMsg.includes('NotAllowedError')) {
                errorMessage = `
                    <div class="webcam-permission-error" style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;">
                        <h3 style="color: #856404; margin-bottom: 15px;">
                            <i class="bx bx-error-circle" style="font-size: 2rem;"></i><br>
                            دسترسی به دوربین رد شد
                        </h3>
                        <p style="color: #856404; margin-bottom: 15px; font-size: 1.1rem;">
                            لطفاً دسترسی دوربین را در مرورگر خود فعال کنید:
                        </p>
                        <div style="text-align: right; background: white; padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <p style="margin: 8px 0;"><strong>Chrome/Edge:</strong> روی آیکون قفل یا دوربین در نوار آدرس کلیک کنید و "Allow" را انتخاب کنید</p>
                            <p style="margin: 8px 0;"><strong>Firefox:</strong> روی آیکون قفل در نوار آدرس کلیک کنید و "Allow" را برای دوربین انتخاب کنید</p>
                            <p style="margin: 8px 0;"><strong>Safari:</strong> Safari > Preferences > Websites > Camera و سپس "Allow" را انتخاب کنید</p>
                        </div>
                        <div style="margin-top: 15px;">
                            <button onclick="retryWebcam()" style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin: 0 5px;">
                                <i class="bx bx-refresh"></i> تلاش مجدد
                            </button>
                            <button onclick="location.reload()" style="padding: 10px 25px; background: #ffc107; color: #856404; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin: 0 5px;">
                                <i class="bx bx-refresh"></i> رفرش صفحه
                            </button>
                        </div>
                    </div>
                `;
            } else if (errorName === 'NotFoundError' || errorMsg.includes('No camera') || errorMsg.includes('NotFoundError')) {
                errorMessage = `
                    <div class="webcam-permission-error" style="background: #f8d7da; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;">
                        <h3 style="color: #721c24; margin-bottom: 15px;">
                            <i class="bx bx-error-circle" style="font-size: 2rem;"></i><br>
                            دوربین یافت نشد
                        </h3>
                        <p style="color: #721c24; margin-bottom: 15px;">
                            لطفاً مطمئن شوید که دوربین به کامپیوتر متصل است و توسط برنامه دیگری استفاده نمی‌شود.
                        </p>
                        <div style="margin-top: 15px;">
                            <button onclick="retryWebcam()" style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin: 0 5px;">
                                <i class="bx bx-refresh"></i> تلاش مجدد
                            </button>
                        </div>
                    </div>
                `;
            } else {
                errorMessage = `
                    <div class="webcam-permission-error" style="background: #f8d7da; border: 2px solid #dc3545; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center;">
                        <h3 style="color: #721c24; margin-bottom: 15px;">
                            <i class="bx bx-error-circle" style="font-size: 2rem;"></i><br>
                            خطا در دسترسی به دوربین
                        </h3>
                        <p style="color: #721c24; margin-bottom: 15px;">
                            ${errorMsg || 'خطای نامشخص در دسترسی به دوربین'}
                        </p>
                        <div style="margin-top: 15px;">
                            <button onclick="retryWebcam()" style="padding: 10px 25px; background: #28a745; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin: 0 5px;">
                                <i class="bx bx-refresh"></i> تلاش مجدد
                            </button>
                            <button onclick="location.reload()" style="padding: 10px 25px; background: #dc3545; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; margin: 0 5px;">
                                <i class="bx bx-refresh"></i> رفرش صفحه
                            </button>
                        </div>
                    </div>
                `;
            }
            
            // Insert error message into camera container
            var cameraContainer = document.querySelector('#my_camera').parentElement;
            if (cameraContainer) {
                var existingError = cameraContainer.querySelector('.webcam-permission-error');
                if (existingError) {
                    existingError.remove();
                }
                cameraContainer.insertAdjacentHTML('afterbegin', errorMessage);
            } else {
                showMessage('خطا در دسترسی به دوربین. لطفاً دسترسی دوربین را در مرورگر خود فعال کنید.', 'error');
            }
        }

        function take_snapshot() {
            // Check if webcam is loaded and live
            if (typeof Webcam === 'undefined' || !Webcam.loaded) {
                showMessage('دوربین هنوز آماده نیست. لطفاً چند لحظه صبر کنید.', 'error');
                return;
            }

            // Show loading
            document.getElementById('results').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="bx bx-loader-alt bx-spin" style="font-size: 2rem; color: #667eea;"></i><p>در حال گرفتن عکس...</p></div>';
            
            try {
                Webcam.snap(function(data_uri) {
                    // Set image value (use jQuery if available, otherwise use vanilla JS)
                    var imageTag = document.querySelector('.image-tag');
                    if (imageTag) {
                        imageTag.value = data_uri;
                    } else if (typeof $ !== 'undefined') {
                        $(".image-tag").val(data_uri);
                    }
                    
                    document.getElementById('results').innerHTML = '<img src="' + data_uri + '" style="max-width: 100%; max-height: 100%; border-radius: 8px;"/>';
                    
                    // Show success message
                    showMessage('عکس با موفقیت گرفته شد!', 'success');
                });
            } catch (error) {
                console.error('Error taking snapshot:', error);
                showMessage('خطا در گرفتن عکس. لطفاً دوباره تلاش کنید.', 'error');
            }
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

        // Initialize webcam when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize webcam
            initializeWebcam();
            
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

        // Fallback: Also try to initialize if DOMContentLoaded already fired
        if (document.readyState === 'loading') {
            // DOMContentLoaded has not fired yet
        } else {
            // DOMContentLoaded has already fired
            initializeWebcam();
        }
    </script>
</body>
</html>
