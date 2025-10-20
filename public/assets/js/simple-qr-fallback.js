// Simple QR Code Fallback Generator
// This creates a basic QR code representation using CSS and HTML
class SimpleQRGenerator {
    constructor() {
        this.size = 80;
        this.margin = 2;
    }

    /**
     * Generate a simple QR-like pattern for the reference number
     * @param {string} text - Text to encode
     * @param {string} elementId - Element ID to insert the QR code
     */
    generateSimpleQR(text, elementId) {
        const element = document.getElementById(elementId);
        if (!element) return;

        // Create a simple pattern based on the text
        const pattern = this.createPattern(text);
        
        // Create QR-like visual representation
        const qrContainer = document.createElement('div');
        qrContainer.style.cssText = `
            width: ${this.size}px;
            height: ${this.size}px;
            background: white;
            border: 1px solid #ccc;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 1px;
            padding: 2px;
        `;

        // Add pattern squares
        for (let i = 0; i < 64; i++) {
            const square = document.createElement('div');
            square.style.cssText = `
                background: ${pattern[i] ? '#000' : '#fff'};
                border-radius: 1px;
            `;
            qrContainer.appendChild(square);
        }

        // Add reference number below
        const refText = document.createElement('div');
        refText.style.cssText = `
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            font-size: 8px;
            text-align: center;
            color: #666;
            word-break: break-all;
        `;
        refText.textContent = text;

        const wrapper = document.createElement('div');
        wrapper.style.cssText = 'position: relative; display: inline-block;';
        wrapper.appendChild(qrContainer);
        wrapper.appendChild(refText);

        element.innerHTML = '';
        element.appendChild(wrapper);
    }

    /**
     * Create a simple pattern based on text hash
     * @param {string} text - Text to create pattern from
     * @returns {Array<boolean>} Pattern array
     */
    createPattern(text) {
        const pattern = [];
        let hash = 0;
        
        // Simple hash function
        for (let i = 0; i < text.length; i++) {
            hash = ((hash << 5) - hash + text.charCodeAt(i)) & 0xffffffff;
        }
        
        // Create 8x8 pattern based on hash
        for (let i = 0; i < 64; i++) {
            pattern.push((hash >> i) & 1 === 1);
        }
        
        return pattern;
    }
}

// Create global instance
window.SimpleQRGenerator = new SimpleQRGenerator();

// Auto-generate simple QR codes for elements with data-simple-qr attribute
document.addEventListener('DOMContentLoaded', function() {
    const qrElements = document.querySelectorAll('[data-simple-qr]');
    
    qrElements.forEach((element) => {
        const text = element.getAttribute('data-simple-qr');
        if (text) {
            window.SimpleQRGenerator.generateSimpleQR(text, element.id);
        }
    });
});
