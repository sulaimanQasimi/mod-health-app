# Tailwind Forms

A beautiful Laravel package providing form components with Tailwind UI styling. This package offers a comprehensive set of form components that are both functional and visually appealing.

## Features

- 🎨 **Beautiful Tailwind UI Components** - Pre-styled form components
- 🔧 **Highly Customizable** - Easy to customize with variants and themes
- 📱 **Responsive Design** - Mobile-first approach
- ♿ **Accessible** - Built with accessibility in mind
- 🚀 **Easy to Use** - Simple Blade component syntax
- 🎯 **Laravel Integration** - Seamless integration with Laravel's form handling
- 📝 **Validation Support** - Built-in error handling and display
- 🎭 **Multiple Variants** - Different styles for different use cases

## Installation

1. Install the package via Composer:

```bash
composer require sulaimanqasimi/router
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag=tailwind-forms:config
```

3. Publish the views (optional, for customization):

```bash
php artisan vendor:publish --tag=tailwind-forms:views
```

4. Publish the assets (optional, for customization):

```bash
php artisan vendor:publish --tag=tailwind-forms:assets
```

## Quick Start

### Basic Form

```blade
<x-tailwind-forms::form method="POST" action="/submit">
    <x-tailwind-forms::form-group label="Name" name="name" required>
        <x-tailwind-forms::input name="name" placeholder="Enter your name" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Email" name="email" required>
        <x-tailwind-forms::input name="email" type="email" placeholder="Enter your email" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::button type="submit">
        Submit
    </x-tailwind-forms::button>
</x-tailwind-forms::form>
```

## Components

### Form

The main form wrapper component.

```blade
<x-tailwind-forms::form 
    method="POST" 
    action="/submit" 
    :has-files="true"
>
    <!-- Form content -->
</x-tailwind-forms::form>
```

**Props:**
- `method` - HTTP method (GET, POST, PUT, PATCH, DELETE)
- `action` - Form action URL
- `has-files` - Enable file uploads (adds enctype="multipart/form-data")

### Input

Text input component with various types.

```blade
<x-tailwind-forms::input 
    name="email" 
    type="email" 
    placeholder="Enter your email"
    required
    variant="error"
/>
```

**Props:**
- `name` - Input name (required)
- `type` - Input type (text, email, password, number, etc.)
- `value` - Default value
- `placeholder` - Placeholder text
- `required` - Make field required
- `disabled` - Disable the input
- `readonly` - Make input readonly
- `variant` - Style variant (default, error, success)

### Textarea

Multi-line text input component.

```blade
<x-tailwind-forms::textarea 
    name="message" 
    placeholder="Enter your message"
    :rows="4"
    required
/>
```

**Props:**
- `name` - Textarea name (required)
- `value` - Default value
- `placeholder` - Placeholder text
- `rows` - Number of rows (default: 3)
- `required` - Make field required
- `disabled` - Disable the textarea
- `readonly` - Make textarea readonly

### Select

Dropdown select component.

```blade
<x-tailwind-forms::select 
    name="country" 
    :options="['us' => 'United States', 'ca' => 'Canada']"
    placeholder="Select a country"
    required
/>
```

**Props:**
- `name` - Select name (required)
- `options` - Array of options (value => label)
- `value` - Default selected value
- `placeholder` - Placeholder option text
- `multiple` - Enable multiple selection
- `required` - Make field required
- `disabled` - Disable the select

### Checkbox

Single checkbox component.

```blade
<x-tailwind-forms::checkbox 
    name="terms" 
    value="1"
    :checked="false"
    required
/>
```

**Props:**
- `name` - Checkbox name (required)
- `value` - Checkbox value (default: 1)
- `checked` - Whether checkbox is checked
- `required` - Make field required
- `disabled` - Disable the checkbox

### Radio

Radio button component.

```blade
<x-tailwind-forms::radio 
    name="gender" 
    value="male"
    :checked-value="old('gender')"
/>
```

**Props:**
- `name` - Radio name (required)
- `value` - Radio value (required)
- `checked-value` - Currently selected value
- `required` - Make field required
- `disabled` - Disable the radio

### Button

Button component with multiple variants.

```blade
<x-tailwind-forms::button 
    type="submit" 
    variant="primary"
>
    Submit
</x-tailwind-forms::button>

<x-tailwind-forms::button 
    variant="secondary"
    href="/cancel"
>
    Cancel
</x-tailwind-forms::button>
```

**Props:**
- `type` - Button type (button, submit, reset)
- `variant` - Style variant (primary, secondary, danger)
- `disabled` - Disable the button
- `href` - Make it a link button

### Label

Label component for form fields.

```blade
<x-tailwind-forms::label for="email" required>
    Email Address
</x-tailwind-forms::label>
```

**Props:**
- `for` - ID of the associated input
- `required` - Show required asterisk

### Error

Error message component.

```blade
<x-tailwind-forms::error name="email" />
```

**Props:**
- `name` - Field name to show errors for
- `message` - Custom error message

### Form Group

Complete form field wrapper with label, input, help text, and error.

```blade
<x-tailwind-forms::form-group 
    label="Email" 
    name="email" 
    help="We'll never share your email"
    required
>
    <x-tailwind-forms::input name="email" type="email" />
</x-tailwind-forms::form-group>
```

**Props:**
- `label` - Field label
- `name` - Field name (for error handling)
- `help` - Help text
- `required` - Make field required

## Configuration

The package configuration is located in `config/tailwind-forms.php`. You can customize:

- Default CSS classes for components
- Component variants
- Theme settings
- Framework preferences

### Customizing Classes

```php
// config/tailwind-forms.php
'default_classes' => [
    'input' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
    'button' => 'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500',
    // ... other components
],
```

### Adding Variants

```php
// config/tailwind-forms.php
'variants' => [
    'input' => [
        'large' => 'block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg px-4 py-3',
        'small' => 'block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-2 py-1',
    ],
],
```

## JavaScript Features

The package includes JavaScript functionality for:

- **File Input Previews** - Show image previews for file uploads
- **Password Toggle** - Show/hide password fields
- **Character Counters** - Count characters in textareas
- **Form Validation** - Client-side validation

### Using JavaScript Features

Include the JavaScript file in your layout:

```blade
<script src="{{ asset('vendor/tailwind-forms/tailwind-forms.js') }}"></script>
```

### Password Toggle

```blade
<div class="relative">
    <x-tailwind-forms::input name="password" type="password" />
    <button type="button" class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <!-- Eye icon will be inserted by JavaScript -->
        </svg>
    </button>
</div>
```

### Character Counter

```blade
<div>
    <x-tailwind-forms::textarea name="message" data-max-length="500" />
    <div class="char-counter text-sm text-gray-500 mt-1"></div>
</div>
```

## Examples

### Contact Form

```blade
<x-tailwind-forms::form method="POST" action="/contact">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-tailwind-forms::form-group label="First Name" name="first_name" required>
            <x-tailwind-forms::input name="first_name" />
        </x-tailwind-forms::form-group>
        
        <x-tailwind-forms::form-group label="Last Name" name="last_name" required>
            <x-tailwind-forms::input name="last_name" />
        </x-tailwind-forms::form-group>
    </div>
    
    <x-tailwind-forms::form-group label="Email" name="email" required>
        <x-tailwind-forms::input name="email" type="email" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Subject" name="subject" required>
        <x-tailwind-forms::input name="subject" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Message" name="message" required>
        <x-tailwind-forms::textarea name="message" :rows="5" />
    </x-tailwind-forms::form-group>
    
    <div class="flex justify-end space-x-3">
        <x-tailwind-forms::button variant="secondary" href="/">
            Cancel
        </x-tailwind-forms::button>
        <x-tailwind-forms::button type="submit">
            Send Message
        </x-tailwind-forms::button>
    </div>
</x-tailwind-forms::form>
```

### User Registration Form

```blade
<x-tailwind-forms::form method="POST" action="/register" :has-files="true">
    <x-tailwind-forms::form-group label="Profile Picture" name="avatar">
        <input type="file" name="avatar" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
        <div class="file-preview mt-2"></div>
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Username" name="username" required>
        <x-tailwind-forms::input name="username" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Email" name="email" required>
        <x-tailwind-forms::input name="email" type="email" />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Password" name="password" required>
        <div class="relative">
            <x-tailwind-forms::input name="password" type="password" />
            <button type="button" class="password-toggle absolute right-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        </div>
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group label="Country" name="country" required>
        <x-tailwind-forms::select 
            name="country" 
            :options="[
                'us' => 'United States',
                'ca' => 'Canada',
                'uk' => 'United Kingdom',
                'au' => 'Australia'
            ]"
            placeholder="Select your country"
        />
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::form-group>
        <div class="flex items-center">
            <x-tailwind-forms::checkbox name="terms" required />
            <x-tailwind-forms::label for="terms" class="ml-2">
                I agree to the <a href="/terms" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a>
            </x-tailwind-forms::label>
        </div>
    </x-tailwind-forms::form-group>
    
    <x-tailwind-forms::button type="submit" class="w-full">
        Create Account
    </x-tailwind-forms::button>
</x-tailwind-forms::form>
```

## Customization

### Custom Component Classes

You can override component classes by publishing the views and modifying them:

```bash
php artisan vendor:publish --tag=tailwind-forms:views
```

Then edit `resources/views/vendor/tailwind-forms/components/input.blade.php`:

```blade
<input
    type="{{ $type }}"
    name="{{ $name }}"
    id="{{ $id }}"
    value="{{ $getOldValue() }}"
    class="your-custom-classes {{ $getClasses() }}"
    @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
/>
```

### Creating Custom Variants

Add custom variants to your configuration:

```php
// config/tailwind-forms.php
'variants' => [
    'input' => [
        'custom' => 'block w-full rounded-lg border-2 border-blue-300 shadow-lg focus:border-blue-500 focus:ring-blue-500 text-lg px-4 py-3 bg-blue-50',
    ],
],
```

Then use it in your components:

```blade
<x-tailwind-forms::input name="email" variant="custom" />
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

If you have any questions or need help, please open an issue on GitHub or contact the maintainers.
