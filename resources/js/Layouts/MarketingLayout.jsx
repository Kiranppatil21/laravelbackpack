import React from 'react';
import { Head } from '@inertiajs/react';

export default function MarketingLayout({ children, title = '' }) {
    return (
        <>
            {title && (
                <Head>
                    <title>{title}</title>
                </Head>
            )}
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <header className="bg-white shadow-sm">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between items-center py-6 md:justify-start md:space-x-10">
                            <div className="flex justify-start lg:w-0 lg:flex-1">
                                <a href="/" className="text-2xl font-bold text-gray-900">
                                    Company Name
                                </a>
                            </div>
                            <nav className="hidden md:flex space-x-10">
                                <a href="/" className="text-base font-medium text-gray-500 hover:text-gray-900">
                                    Home
                                </a>
                                <a href="/careers" className="text-base font-medium text-gray-900">
                                    Careers
                                </a>
                                <a href="/contact" className="text-base font-medium text-gray-500 hover:text-gray-900">
                                    Contact
                                </a>
                            </nav>
                        </div>
                    </div>
                </header>

                {/* Main Content */}
                <main className="flex-1">
                    {children}
                </main>

                {/* Footer */}
                <footer className="bg-white">
                    <div className="max-w-7xl mx-auto py-12 px-4 sm:px-6 md:flex md:items-center md:justify-between lg:px-8">
                        <div className="flex justify-center space-x-6 md:order-2">
                            <a href="#" className="text-gray-400 hover:text-gray-500">
                                <span className="sr-only">Twitter</span>
                                <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                </svg>
                            </a>
                            <a href="#" className="text-gray-400 hover:text-gray-500">
                                <span className="sr-only">LinkedIn</span>
                                <svg className="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fillRule="evenodd" d="M19 0H5a5 5 0 00-5 5v14a5 5 0 005 5h14a5 5 0 005-5V5a5 5 0 00-5-5zM8 19H5V8h3v11zM6.5 6.732c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zM20 19h-3v-5.604c0-3.368-4-3.113-4 0V19h-3V8h3v1.765c1.396-2.586 7-2.777 7 2.476V19z" clipRule="evenodd" />
                                </svg>
                            </a>
                        </div>
                        <div className="mt-8 md:mt-0 md:order-1">
                            <p className="text-center text-base text-gray-400">
                                &copy; 2024 Company Name, Inc. All rights reserved.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}