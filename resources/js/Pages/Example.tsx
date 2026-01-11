import { Head } from '@inertiajs/react';
import { Button, Card, Alert } from 'flowbite-react';

export default function Example() {
    return (
        <>
            <Head title="Flowbite Example" />
            <div className="min-h-screen bg-gray-50 py-8">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h1 className="text-3xl font-bold text-gray-900 mb-8">
                        Flowbite React Components Example
                    </h1>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <Card>
                            <h5 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                Card Component
                            </h5>
                            <p className="font-normal text-gray-700 dark:text-gray-400">
                                This is a Flowbite card component styled with Tailwind CSS.
                            </p>
                        </Card>

                        <Card>
                            <h5 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                                Buttons
                            </h5>
                            <div className="flex gap-2 mt-4">
                                <Button>Default</Button>
                                <Button color="blue">Blue</Button>
                                <Button color="green">Green</Button>
                            </div>
                        </Card>
                    </div>

                    <Alert color="info" className="mb-6">
                        <span className="font-medium">Info alert!</span> This is a Flowbite alert component.
                    </Alert>

                    <div className="bg-white rounded-lg shadow p-6">
                        <h2 className="text-xl font-semibold mb-4">Tailwind CSS Classes</h2>
                        <p className="text-gray-600 mb-4">
                            You can use Tailwind CSS utility classes alongside Flowbite components.
                        </p>
                        <div className="flex flex-wrap gap-2">
                            <span className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                Tailwind
                            </span>
                            <span className="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm">
                                Flowbite
                            </span>
                            <span className="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-sm">
                                React
                            </span>
                            <span className="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                Inertia.js
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
