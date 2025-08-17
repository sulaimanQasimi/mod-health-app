# WebcamJS Library

This directory contains the WebcamJS library files for the patient photo capture functionality.

## Files Included:

- `webcam.min.js` - Minified version of WebcamJS (recommended for production)
- `webcam.js` - Full version of WebcamJS (for development/debugging)
- `webcam.swf` - Flash fallback file (for older browsers)
- `flash/` - Flash source files and components

## Installation:

The library was installed via npm:
```bash
npm install webcamjs --save --legacy-peer-deps
```

## Usage:

In your Blade templates, include the library like this:
```html
<script src="{{ asset('assets/vendor/libs/webcamjs/webcam.min.js') }}"></script>
```

## Features:

- Real-time webcam capture
- Image format support (JPEG, PNG)
- Quality settings
- Flash fallback for older browsers
- Cross-browser compatibility

## Dependencies:

- jQuery (already included in the project)

## Version:

- WebcamJS: 1.0.26
- Installed via npm on: {{ date('Y-m-d H:i:s') }}
