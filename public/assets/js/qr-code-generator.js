// QR Code Generator using qrcode npm package
import QRCode from 'qrcode';

class QRCodeGenerator {
    constructor() {
        this.defaultOptions = {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            },
            errorCorrectionLevel: 'M'
        };
    }

    /**
     * Generate QR code as data URL
     * @param {string} text - Text to encode
     * @param {object} options - QR code options
     * @returns {Promise<string>} Data URL of the QR code
     */
    async generateDataURL(text, options = {}) {
        try {
            const mergedOptions = { ...this.defaultOptions, ...options };
            return await QRCode.toDataURL(text, mergedOptions);
        } catch (error) {
            console.error('Error generating QR code:', error);
            return null;
        }
    }

    /**
     * Generate QR code as SVG
     * @param {string} text - Text to encode
     * @param {object} options - QR code options
     * @returns {Promise<string>} SVG string of the QR code
     */
    async generateSVG(text, options = {}) {
        try {
            const mergedOptions = { ...this.defaultOptions, ...options };
            return await QRCode.toString(text, { type: 'svg', ...mergedOptions });
        } catch (error) {
            console.error('Error generating QR code SVG:', error);
            return null;
        }
    }

    /**
     * Generate QR code and insert into element
     * @param {string} text - Text to encode
     * @param {string|HTMLElement} element - Element selector or element
     * @param {object} options - QR code options
     */
    async generateToElement(text, element, options = {}) {
        try {
            const targetElement = typeof element === 'string' ? document.querySelector(element) : element;
            if (!targetElement) {
                console.error('Target element not found');
                return;
            }

            const dataURL = await this.generateDataURL(text, options);
            if (dataURL) {
                const img = document.createElement('img');
                img.src = dataURL;
                img.alt = 'QR Code';
                img.style.maxWidth = '100%';
                img.style.height = 'auto';
                
                // Clear existing content
                targetElement.innerHTML = '';
                targetElement.appendChild(img);
            }
        } catch (error) {
            console.error('Error generating QR code to element:', error);
        }
    }

    /**
     * Generate QR code for print reports
     * @param {string} referenceNumber - Reference number to encode
     * @param {string} elementId - ID of the element to insert QR code
     */
    async generateForReport(referenceNumber, elementId) {
        const printOptions = {
            width: 80,
            margin: 1,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            },
            errorCorrectionLevel: 'H' // High error correction for small size
        };

        await this.generateToElement(referenceNumber, `#${elementId}`, printOptions);
    }
}

// Create global instance
window.QRCodeGenerator = new QRCodeGenerator();

// Auto-generate QR codes for elements with data-qr attribute
document.addEventListener('DOMContentLoaded', function() {
    const qrElements = document.querySelectorAll('[data-qr]');
    
    qrElements.forEach(async (element) => {
        const text = element.getAttribute('data-qr');
        if (text) {
            await window.QRCodeGenerator.generateToElement(text, element);
        }
    });
});

export default QRCodeGenerator;
