import React from 'react';
import { Head } from '@inertiajs/react';

export default function AdminLayout({ header, children }) {
    return (
        <div className="min-h-screen bg-gray-100">
            {/* Admin Header */}
            <nav className="border-b border-gray-100 bg-white shadow">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex items-center">
                            <div className="flex-shrink-0">
                                <h1 className="text-xl font-semibold text-gray-800">
                                    Admin Panel - Job Openings
                                </h1>
                            </div>
                        </div>
                        <div className="flex items-center space-x-4">
                            <a
                                href="/admin/dashboard"
                                className="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                            >
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Page Header */}
            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            {/* Main Content */}
            <main className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {children}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    );
}