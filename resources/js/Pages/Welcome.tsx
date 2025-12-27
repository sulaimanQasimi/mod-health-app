import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-gray-100 flex items-center justify-center">
                <div className="max-w-2xl mx-auto px-4 py-16">
                    <div className="bg-white rounded-lg shadow-lg p-8">
                        <h1 className="text-4xl font-bold text-gray-900 mb-4">
                            Welcome to Inertia.js + React + TypeScript
                        </h1>
                        <p className="text-gray-600 mb-6">
                            Your Laravel application is now set up with Inertia.js, React, and TypeScript!
                        </p>
                        <div className="space-y-4">
                            <div className="bg-blue-50 border border-blue-200 rounded p-4">
                                <h2 className="font-semibold text-blue-900 mb-2">What's Next?</h2>
                                <ul className="list-disc list-inside text-blue-800 space-y-1">
                                    <li>Create your React components in <code className="bg-blue-100 px-1 rounded">resources/js/Pages</code></li>
                                    <li>Use Inertia's router to navigate between pages</li>
                                    <li>Share data from Laravel controllers using Inertia::render()</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
