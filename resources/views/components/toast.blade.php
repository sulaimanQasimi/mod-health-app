 @if ($message = Session::pull('success'))
     <!-- Toast with Animation -->
     <div class="bs-toast toast toast-ex animate__animated my-2 fade bg-success animate__bounceIn show" id="show-hide-toast"
          role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
         <div class="toast-header">
             <i class="bx bx-bell me-2 display-6"></i>
             <div class="me-auto fw-semibold display-6">{{ localize('global.notification_success') }}</div>
         </div>
         <div class="toast-body">{{ $message }}</div>
     </div>
 @endif
 <!--/ Toast with Animation -->

 @if ($message = Session::pull('error'))
     <!-- Toast with Animation -->
     <div class="bs-toast toast toast-ex animate__animated my-2 fade bg-danger animate__bounceIn show" id="show-hide-toast"
          role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
         <div class="toast-header">
             <i class="bx bx-bell me-2"></i>
             <div class="me-auto fw-semibold">{{ localize('global.notification_error') }}</div>
         </div>
         <div class="toast-body">{{ $message }}</div>
     </div>
 @endif
 <!--/ Toast with Animation -->

 @section('scripts')
     <script>
         $('#show-hide-toast').delay(2000).fadeOut(400);
     </script>
 @endsection
