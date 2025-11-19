import React from 'react';
import { Head } from '@inertiajs/react';

export default function TestPage() {
    return (
        <div>
            <Head>
                <title>Test Page</title>
            </Head>
            <div className="p-8">
                <h1 className="text-2xl font-bold">Test Page Working!</h1>
                <p>If you can see this, React and Inertia are working correctly.</p>
            </div>
        </div>
    );
}