// Debug script for lab section
console.log('Debug script loaded');

// Check if Vue is available
if (typeof Vue !== 'undefined') {
    console.log('Vue is available:', Vue);
} else {
    console.log('Vue is not available');
}

// Check if createApp is available
if (typeof createApp !== 'undefined') {
    console.log('createApp is available');
} else {
    console.log('createApp is not available');
}

// Check if window.createApp is available
if (typeof window.createApp !== 'undefined') {
    console.log('window.createApp is available');
} else {
    console.log('window.createApp is not available');
}

// Check DOM elements
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    const labContainer = document.getElementById('lab-section-container');
    if (labContainer) {
        console.log('Lab container found:', labContainer);
        console.log('Appointment data:', labContainer.dataset.appointment);
        console.log('Permissions data:', labContainer.dataset.permissions);
    } else {
        console.log('Lab container not found');
    }
    
    // Check for any Vue errors
    window.addEventListener('error', function(e) {
        if (e.message.includes('Vue') || e.message.includes('createApp')) {
            console.error('Vue error detected:', e);
        }
    });
});
