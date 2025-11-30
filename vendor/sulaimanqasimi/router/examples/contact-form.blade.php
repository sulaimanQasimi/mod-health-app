{{-- Contact Form Example --}}
<x-tailwind-forms::form method="POST" action="/contact" class="max-w-2xl mx-auto">
    <div class="bg-white shadow-lg rounded-lg p-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Us</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-tailwind-forms::form-group label="First Name" name="first_name" required>
                <x-tailwind-forms::input name="first_name" placeholder="John" />
            </x-tailwind-forms::form-group>
            
            <x-tailwind-forms::form-group label="Last Name" name="last_name" required>
                <x-tailwind-forms::input name="last_name" placeholder="Doe" />
            </x-tailwind-forms::form-group>
        </div>
        
        <x-tailwind-forms::form-group label="Email" name="email" required>
            <x-tailwind-forms::input name="email" type="email" placeholder="john@example.com" />
        </x-tailwind-forms::form-group>
        
        <x-tailwind-forms::form-group label="Phone" name="phone">
            <x-tailwind-forms::input name="phone" type="tel" placeholder="+1 (555) 123-4567" />
        </x-tailwind-forms::form-group>
        
        <x-tailwind-forms::form-group label="Subject" name="subject" required>
            <x-tailwind-forms::select 
                name="subject" 
                :options="[
                    'general' => 'General Inquiry',
                    'support' => 'Technical Support',
                    'sales' => 'Sales Question',
                    'feedback' => 'Feedback',
                    'other' => 'Other'
                ]"
                placeholder="Select a subject"
            />
        </x-tailwind-forms::form-group>
        
        <x-tailwind-forms::form-group label="Message" name="message" required>
            <x-tailwind-forms::textarea 
                name="message" 
                :rows="5" 
                placeholder="Please describe your inquiry in detail..."
                data-max-length="1000"
            />
            <div class="char-counter text-sm text-gray-500 mt-1"></div>
        </x-tailwind-forms::form-group>
        
        <x-tailwind-forms::form-group>
            <div class="flex items-center">
                <x-tailwind-forms::checkbox name="newsletter" />
                <x-tailwind-forms::label for="newsletter" class="ml-2">
                    Subscribe to our newsletter for updates and promotions
                </x-tailwind-forms::label>
            </div>
        </x-tailwind-forms::form-group>
        
        <div class="flex justify-end space-x-3 mt-8">
            <x-tailwind-forms::button variant="secondary" href="/">
                Cancel
            </x-tailwind-forms::button>
            <x-tailwind-forms::button type="submit">
                Send Message
            </x-tailwind-forms::button>
        </div>
    </div>
</x-tailwind-forms::form>
