import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function Documentation() {
    const [activeSection, setActiveSection] = useState('getting-started');
    const [selectedArticle, setSelectedArticle] = useState(null);

    const sections = [
        {
            id: 'getting-started',
            title: 'Getting Started',
            icon: '🚀',
            articles: [
                { 
                    id: 'quick-start',
                    title: 'Quick Start Guide', 
                    description: 'Get up and running in 15 minutes',
                    readTime: '15 min',
                    content: {
                        overview: 'This guide will help you set up your SecureServe account and start managing your security operations within 15 minutes.',
                        steps: [
                            {
                                title: 'Account Creation',
                                content: 'Sign up at SecureServe.com with your business email and create your agency profile.',
                                tips: ['Use a business email for better credibility', 'Complete all profile fields for full features']
                            },
                            {
                                title: 'Initial Setup',
                                content: 'Configure your agency locations, add your first client, and set up basic settings.',
                                tips: ['Add at least one location to enable GPS tracking', 'Upload your agency logo for branded reports']
                            },
                            {
                                title: 'Add Your First Employee',
                                content: 'Create employee profiles with photos, documents, and assign them to locations.',
                                tips: ['Collect all documents before adding employees', 'Take clear photos for better identification']
                            },
                            {
                                title: 'Mobile App Setup',
                                content: 'Download the SecureServe mobile app and train your guards on attendance marking.',
                                tips: ['Test GPS accuracy at each location', 'Ensure guards have reliable internet connection']
                            }
                        ]
                    }
                },
                { 
                    id: 'system-requirements',
                    title: 'System Requirements', 
                    description: 'Hardware and software requirements',
                    readTime: '5 min',
                    content: {
                        overview: 'SecureServe is designed to work on modern devices and browsers. Here are the minimum requirements.',
                        requirements: {
                            web: {
                                title: 'Web Application',
                                items: [
                                    'Modern web browser (Chrome 90+, Firefox 88+, Safari 14+)',
                                    'Internet connection (minimum 1 Mbps)',
                                    'Screen resolution 1024x768 or higher',
                                    'JavaScript enabled'
                                ]
                            },
                            mobile: {
                                title: 'Mobile Application',
                                items: [
                                    'Android 8.0+ or iOS 12.0+',
                                    'GPS capability',
                                    'Camera for photo capture',
                                    '100 MB free storage space',
                                    '3G/4G/WiFi connectivity'
                                ]
                            },
                            recommended: {
                                title: 'Recommended Specifications',
                                items: [
                                    'High-speed internet (5+ Mbps)',
                                    'Modern smartphone with good camera',
                                    'Reliable GPS signal at guard locations',
                                    'Backup internet connection'
                                ]
                            }
                        }
                    }
                },
                { 
                    id: 'account-setup',
                    title: 'Account Setup', 
                    description: 'Setting up your agency account',
                    readTime: '10 min',
                    content: {
                        overview: 'Complete account setup to unlock all SecureServe features and ensure smooth operations.',
                        sections: [
                            {
                                title: 'Company Information',
                                content: 'Add your agency details, license information, and contact data.',
                                fields: ['Agency Name', 'License Number', 'GST Registration', 'Contact Details', 'Business Address']
                            },
                            {
                                title: 'Payment Setup',
                                content: 'Configure billing information and select your subscription plan.',
                                fields: ['Billing Address', 'Payment Method', 'Plan Selection', 'Invoice Preferences']
                            },
                            {
                                title: 'User Accounts',
                                content: 'Create additional admin users and set their permissions.',
                                fields: ['Admin Users', 'Role Assignment', 'Access Permissions', 'Notification Settings']
                            }
                        ]
                    }
                },
                { 
                    id: 'initial-configuration',
                    title: 'Initial Configuration', 
                    description: 'Basic configuration steps',
                    readTime: '12 min',
                    content: {
                        overview: 'Configure essential settings to tailor SecureServe to your agency\'s specific needs.',
                        configurations: [
                            {
                                category: 'Location Settings',
                                items: [
                                    'Add all service locations with GPS coordinates',
                                    'Set GPS radius for each location (recommended: 50 meters)',
                                    'Configure working hours for each location',
                                    'Set up location-specific rules and requirements'
                                ]
                            },
                            {
                                category: 'Payroll Settings',
                                items: [
                                    'Configure salary structures and components',
                                    'Set up PF and ESIC registration details',
                                    'Define overtime rules and holiday policies',
                                    'Configure professional tax slabs for different states'
                                ]
                            },
                            {
                                category: 'Notification Settings',
                                items: [
                                    'Enable SMS and email notifications',
                                    'Configure alert recipients for different events',
                                    'Set up escalation rules for missed attendance',
                                    'Customize notification templates'
                                ]
                            }
                        ]
                    }
                }
            ]
        },
        {
            id: 'employee-management',
            title: 'Employee Management',
            icon: '👥',
            articles: [
                { 
                    id: 'adding-employees',
                    title: 'Adding Employees', 
                    description: 'Complete guide to employee onboarding',
                    readTime: '8 min',
                    content: {
                        overview: 'Learn how to efficiently add and onboard new security guards and staff members.',
                        process: [
                            {
                                step: 'Personal Information',
                                details: 'Enter basic details like name, contact information, and emergency contacts.',
                                required: ['Full Name', 'Phone Number', 'Email (optional)', 'Address', 'Emergency Contact']
                            },
                            {
                                step: 'Documents Upload',
                                details: 'Upload and verify identity and qualification documents.',
                                required: ['Aadhaar Card', 'PAN Card', 'Photo', 'Bank Details', 'Experience Certificates']
                            },
                            {
                                step: 'Job Assignment',
                                details: 'Assign locations, shifts, and reporting managers.',
                                required: ['Primary Location', 'Shift Timings', 'Reporting Manager', 'Role Assignment']
                            },
                            {
                                step: 'Salary Configuration',
                                details: 'Set up compensation structure and compliance details.',
                                required: ['Basic Salary', 'Allowances', 'PF Number', 'ESIC Number', 'Bank Account']
                            }
                        ]
                    }
                },
                { 
                    id: 'role-management',
                    title: 'Role Management', 
                    description: 'Setting up roles and permissions',
                    readTime: '6 min',
                    content: {
                        overview: 'Configure different user roles and their access permissions for secure and organized operations.',
                        roles: [
                            {
                                role: 'Security Guard',
                                permissions: ['Mark Attendance', 'View Own Schedule', 'Report Incidents', 'Update Profile'],
                                description: 'Basic field staff with mobile app access for attendance and incident reporting.'
                            },
                            {
                                role: 'Supervisor',
                                permissions: ['Manage Team Attendance', 'View Reports', 'Approve Leave', 'Incident Management'],
                                description: 'Field supervisors who oversee security guards and manage day-to-day operations.'
                            },
                            {
                                role: 'HR Manager',
                                permissions: ['Employee Management', 'Payroll Processing', 'Document Management', 'Compliance Reports'],
                                description: 'HR staff responsible for employee lifecycle and compliance management.'
                            },
                            {
                                role: 'Agency Owner',
                                permissions: ['Full System Access', 'User Management', 'Billing', 'System Configuration'],
                                description: 'Complete administrative access to all system features and settings.'
                            }
                        ]
                    }
                }
            ]
        },
        {
            id: 'attendance',
            title: 'Attendance & GPS',
            icon: '📍',
            articles: [
                { 
                    id: 'gps-setup',
                    title: 'GPS Setup', 
                    description: 'Configure GPS zones and accuracy',
                    readTime: '7 min',
                    content: {
                        overview: 'Set up accurate GPS zones for reliable attendance tracking at all your service locations.',
                        setup: [
                            {
                                step: 'Location Mapping',
                                details: 'Add precise GPS coordinates for each client location.',
                                tips: ['Use Google Maps for accurate coordinates', 'Visit locations to verify GPS accuracy']
                            },
                            {
                                step: 'Radius Configuration',
                                details: 'Set appropriate GPS radius for each location (recommended: 50-100 meters).',
                                tips: ['Consider building size and layout', 'Account for GPS accuracy variations']
                            },
                            {
                                step: 'Testing & Validation',
                                details: 'Test GPS accuracy with actual guards at each location.',
                                tips: ['Test during different times of day', 'Check accuracy in various weather conditions']
                            }
                        ]
                    }
                },
                { 
                    id: 'mobile-app-usage',
                    title: 'Mobile App Usage', 
                    description: 'How employees use the mobile app',
                    readTime: '10 min',
                    content: {
                        overview: 'Complete guide for security guards on using the SecureServe mobile application.',
                        features: [
                            {
                                feature: 'Attendance Marking',
                                steps: [
                                    'Open SecureServe app and login with credentials',
                                    'Ensure GPS is enabled and location permissions granted',
                                    'Tap "Check In" when arriving at assigned location',
                                    'Take selfie for verification if prompted',
                                    'Tap "Check Out" when leaving location'
                                ]
                            },
                            {
                                feature: 'Incident Reporting',
                                steps: [
                                    'Tap "Report Incident" from main menu',
                                    'Select incident type and severity level',
                                    'Add detailed description and photos',
                                    'Submit report for immediate escalation'
                                ]
                            },
                            {
                                feature: 'Schedule Viewing',
                                steps: [
                                    'Check daily and weekly schedules',
                                    'View shift timings and location details',
                                    'Receive notifications for schedule changes'
                                ]
                            }
                        ]
                    }
                }
            ]
        },
        {
            id: 'payroll',
            title: 'Payroll & Compliance',
            icon: '💰',
            articles: [
                { 
                    id: 'payroll-setup',
                    title: 'Payroll Setup', 
                    description: 'Configure salary structures and rules',
                    readTime: '12 min',
                    content: {
                        overview: 'Set up comprehensive payroll system compliant with Indian labor laws and regulations.',
                        components: [
                            {
                                component: 'Salary Structure',
                                details: 'Configure basic salary, allowances, and deductions.',
                                elements: ['Basic Salary (40-50% of CTC)', 'HRA', 'Conveyance Allowance', 'Special Allowance', 'Performance Incentive']
                            },
                            {
                                component: 'Statutory Compliance',
                                details: 'Set up PF, ESIC, and professional tax calculations.',
                                elements: ['PF: 12% employee + 12% employer', 'ESIC: 0.75% employee + 3.25% employer', 'Professional Tax (state-wise)', 'TDS calculations']
                            },
                            {
                                component: 'Attendance Integration',
                                details: 'Link attendance data with payroll calculations.',
                                elements: ['Overtime calculations', 'Leave deductions', 'Late coming penalties', 'Bonus calculations based on attendance']
                            }
                        ]
                    }
                },
                { 
                    id: 'indian-compliance',
                    title: 'Indian Compliance', 
                    description: 'PF, ESIC, and tax calculations',
                    readTime: '15 min',
                    content: {
                        overview: 'Ensure full compliance with Indian labor laws and statutory requirements.',
                        compliance: [
                            {
                                law: 'Provident Fund (PF)',
                                details: 'Mandatory for employees earning up to ₹15,000 per month.',
                                calculation: 'Employee: 12% of basic salary, Employer: 12% of basic salary',
                                requirements: ['PF registration number', 'Employee PF number', 'Monthly ECR filing', 'Annual return filing']
                            },
                            {
                                law: 'Employee State Insurance (ESIC)',
                                details: 'Medical benefits for employees earning up to ₹25,000 per month.',
                                calculation: 'Employee: 0.75% of gross salary, Employer: 3.25% of gross salary',
                                requirements: ['ESIC registration', 'Employee ESIC number', 'Half-yearly returns', 'Contribution payments']
                            },
                            {
                                law: 'Professional Tax',
                                details: 'State-wise tax on employment, varies by state.',
                                calculation: 'Based on monthly gross salary slabs (varies by state)',
                                requirements: ['State registration', 'Monthly deductions', 'Quarterly returns', 'Annual compliance']
                            }
                        ]
                    }
                }
            ]
        },
        {
            id: 'visitor-management',
            title: 'Visitor Management',
            icon: '🏢',
            articles: [
                { 
                    id: 'visitor-registration',
                    title: 'Visitor Registration', 
                    description: 'Set up visitor check-in process',
                    readTime: '8 min',
                    content: {
                        overview: 'Configure comprehensive visitor management system for enhanced security and tracking.',
                        process: [
                            {
                                stage: 'Pre-Registration',
                                details: 'Set up advance visitor registration system.',
                                features: ['Online visitor pre-registration', 'Host approval workflow', 'Visitor notification system', 'Document pre-verification']
                            },
                            {
                                stage: 'Check-In Process',
                                details: 'Streamlined visitor arrival and verification.',
                                features: ['QR code scanning', 'Photo capture', 'ID verification', 'Badge printing', 'Host notification']
                            },
                            {
                                stage: 'Security Screening',
                                details: 'Enhanced security checks and watchlist screening.',
                                features: ['Government ID verification', 'Watchlist checking', 'Purpose verification', 'Escort requirements']
                            },
                            {
                                stage: 'Check-Out Process',
                                details: 'Efficient visitor departure tracking.',
                                features: ['Badge return', 'Exit time logging', 'Feedback collection', 'Visit summary']
                            }
                        ]
                    }
                }
            ]
        },
        {
            id: 'api',
            title: 'API Documentation',
            icon: '🔌',
            articles: [
                { 
                    id: 'api-overview',
                    title: 'API Overview', 
                    description: 'Introduction to SecureServe API',
                    readTime: '10 min',
                    content: {
                        overview: 'SecureServe provides comprehensive REST API for integration with third-party systems.',
                        features: [
                            {
                                category: 'Employee Management APIs',
                                endpoints: ['GET /api/employees', 'POST /api/employees', 'PUT /api/employees/{id}', 'DELETE /api/employees/{id}'],
                                description: 'Manage employee data, documents, and assignments programmatically.'
                            },
                            {
                                category: 'Attendance APIs',
                                endpoints: ['GET /api/attendance', 'POST /api/attendance/checkin', 'POST /api/attendance/checkout', 'GET /api/reports/attendance'],
                                description: 'Access attendance data and generate custom reports.'
                            },
                            {
                                category: 'Payroll APIs',
                                endpoints: ['GET /api/payroll/summary', 'POST /api/payroll/calculate', 'GET /api/payroll/reports', 'POST /api/payroll/process'],
                                description: 'Integrate payroll processing with external accounting systems.'
                            }
                        ]
                    }
                },
                { 
                    id: 'api-authentication',
                    title: 'Authentication', 
                    description: 'API authentication methods',
                    readTime: '7 min',
                    content: {
                        overview: 'Secure your API integrations with proper authentication and authorization.',
                        methods: [
                            {
                                method: 'API Key Authentication',
                                description: 'Simple authentication using API keys for server-to-server communication.',
                                implementation: 'Include API key in request header: Authorization: Bearer YOUR_API_KEY',
                                security: 'Store API keys securely and rotate them regularly.'
                            },
                            {
                                method: 'OAuth 2.0',
                                description: 'Industry-standard authorization framework for secure API access.',
                                implementation: 'Use authorization code flow for user-based access.',
                                security: 'Implement proper scope management and token refresh strategies.'
                            }
                        ]
                    }
                }
            ]
        }
    ];

    const currentSection = sections.find(section => section.id === activeSection);

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "SecureServe Documentation - Complete User Guide",
        "description": "Comprehensive documentation for SecureServe security management platform including setup guides, API documentation, and best practices.",
        "author": {
            "@type": "Organization",
            "name": "SecureServe"
        },
        "publisher": {
            "@type": "Organization",
            "name": "SecureServe"
        },
        "datePublished": "2025-01-01",
        "dateModified": "2025-11-10"
    };

    return (
        <MarketingLayout 
            title="Documentation - Complete User Guide | SecureServe"
            description="Comprehensive documentation for SecureServe security management platform. Learn how to set up employees, manage attendance, process payroll, and more."
            keywords="SecureServe documentation, user guide, security software manual, employee management guide, API documentation, setup guide"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Documentation
                    </h1>
                    <p className="text-xl text-blue-100 mb-8">
                        Everything you need to know about using SecureServe effectively
                    </p>
                    
                    {/* Search Bar */}
                    <div className="relative max-w-2xl mx-auto">
                        <input
                            type="text"
                            placeholder="Search documentation..."
                            className="w-full px-6 py-4 pr-12 text-lg rounded-xl border-0 focus:ring-2 focus:ring-blue-300"
                        />
                        <div className="absolute inset-y-0 right-0 flex items-center pr-4">
                            <svg className="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            {/* Popular Docs */}
            <section className="py-16 bg-gray-50">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 className="text-2xl font-bold text-center mb-8">Popular Documentation</h2>
                    <div className="grid md:grid-cols-3 gap-6">
                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">🚀</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Quick Start Guide</h3>
                            <p className="text-gray-600 mb-4">Get your security agency running on SecureServe in under 15 minutes.</p>
                            <button className="text-blue-600 hover:text-blue-700 font-medium">
                                Read Guide →
                            </button>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">📱</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">Mobile App Guide</h3>
                            <p className="text-gray-600 mb-4">Complete guide for security guards using the mobile application.</p>
                            <button className="text-green-600 hover:text-green-700 font-medium">
                                Read Guide →
                            </button>
                        </div>

                        <div className="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                                <span className="text-2xl">🔌</span>
                            </div>
                            <h3 className="text-lg font-semibold mb-2">API Reference</h3>
                            <p className="text-gray-600 mb-4">Integrate SecureServe with your existing systems using our API.</p>
                            <button className="text-purple-600 hover:text-purple-700 font-medium">
                                View API →
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Main Documentation */}
            <section className="py-16">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col lg:flex-row gap-8">
                        {/* Sidebar Navigation */}
                        <div className="lg:w-1/4">
                            <div className="sticky top-8">
                                <h3 className="text-lg font-semibold mb-4">Documentation Sections</h3>
                                <nav className="space-y-2">
                                    {sections.map((section) => (
                                        <button
                                            key={section.id}
                                            onClick={() => setActiveSection(section.id)}
                                            className={`w-full text-left px-4 py-3 rounded-lg transition-colors ${
                                                activeSection === section.id
                                                    ? 'bg-blue-600 text-white'
                                                    : 'bg-gray-100 hover:bg-gray-200 text-gray-700'
                                            }`}
                                        >
                                            <div className="flex items-center">
                                                <span className="text-xl mr-3">{section.icon}</span>
                                                <span>{section.title}</span>
                                            </div>
                                        </button>
                                    ))}
                                </nav>
                            </div>
                        </div>

                        {/* Content Area */}
                        <div className="lg:w-3/4">
                            {selectedArticle ? (
                                // Article Detail View
                                <div>
                                    <div className="mb-6">
                                        <button 
                                            onClick={() => setSelectedArticle(null)}
                                            className="text-blue-600 hover:text-blue-700 mb-4 flex items-center"
                                        >
                                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                                            </svg>
                                            Back to Documentation
                                        </button>
                                        <h1 className="text-4xl font-bold mb-4">{selectedArticle.title}</h1>
                                        <div className="flex items-center text-gray-500 text-sm mb-6">
                                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {selectedArticle.readTime} read
                                        </div>
                                        <p className="text-xl text-gray-600">{selectedArticle.content.overview}</p>
                                    </div>

                                    {/* Article Content */}
                                    <div className="prose prose-lg max-w-none">
                                        {/* Quick Start Guide Content */}
                                        {selectedArticle.id === 'quick-start' && (
                                            <div>
                                                {selectedArticle.content.steps.map((step, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">
                                                            Step {index + 1}: {step.title}
                                                        </h3>
                                                        <p className="text-gray-700 mb-4">{step.content}</p>
                                                        <div className="bg-blue-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-blue-800 mb-2">💡 Tips:</h4>
                                                            <ul className="text-blue-700">
                                                                {step.tips.map((tip, tipIndex) => (
                                                                    <li key={tipIndex}>• {tip}</li>
                                                                ))}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* System Requirements Content */}
                                        {selectedArticle.id === 'system-requirements' && (
                                            <div>
                                                {Object.entries(selectedArticle.content.requirements).map(([key, req]) => (
                                                    <div key={key} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{req.title}</h3>
                                                        <ul className="space-y-2">
                                                            {req.items.map((item, index) => (
                                                                <li key={index} className="flex items-start">
                                                                    <svg className="w-5 h-5 text-green-500 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                    <span className="text-gray-700">{item}</span>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Account Setup Content */}
                                        {selectedArticle.id === 'account-setup' && (
                                            <div>
                                                {selectedArticle.content.sections.map((section, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{section.title}</h3>
                                                        <p className="text-gray-700 mb-4">{section.content}</p>
                                                        <div className="bg-gray-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold mb-2">Required Fields:</h4>
                                                            <div className="grid md:grid-cols-2 gap-2">
                                                                {section.fields.map((field, fieldIndex) => (
                                                                    <span key={fieldIndex} className="text-sm bg-white px-3 py-1 rounded border">
                                                                        {field}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Other article types with similar patterns */}
                                        {selectedArticle.id === 'initial-configuration' && (
                                            <div>
                                                {selectedArticle.content.configurations.map((config, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{config.category}</h3>
                                                        <ul className="space-y-3">
                                                            {config.items.map((item, itemIndex) => (
                                                                <li key={itemIndex} className="flex items-start">
                                                                    <svg className="w-5 h-5 text-blue-500 mr-3 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <span className="text-gray-700">{item}</span>
                                                                </li>
                                                            ))}
                                                        </ul>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Employee Management Articles */}
                                        {selectedArticle.id === 'adding-employees' && (
                                            <div>
                                                {selectedArticle.content.process.map((step, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">
                                                            {index + 1}. {step.step}
                                                        </h3>
                                                        <p className="text-gray-700 mb-4">{step.details}</p>
                                                        <div className="bg-yellow-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-yellow-800 mb-2">Required Information:</h4>
                                                            <div className="grid md:grid-cols-2 gap-2">
                                                                {step.required.map((req, reqIndex) => (
                                                                    <span key={reqIndex} className="text-sm bg-white px-3 py-1 rounded border text-yellow-700">
                                                                        {req}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {selectedArticle.id === 'role-management' && (
                                            <div>
                                                {selectedArticle.content.roles.map((role, index) => (
                                                    <div key={index} className="mb-8 border rounded-lg p-6">
                                                        <h3 className="text-2xl font-semibold mb-2">{role.role}</h3>
                                                        <p className="text-gray-600 mb-4">{role.description}</p>
                                                        <h4 className="font-semibold mb-2">Permissions:</h4>
                                                        <div className="flex flex-wrap gap-2">
                                                            {role.permissions.map((permission, permIndex) => (
                                                                <span key={permIndex} className="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm">
                                                                    {permission}
                                                                </span>
                                                            ))}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Continue with similar patterns for other article types */}
                                        
                                        {/* GPS Setup Article */}
                                        {selectedArticle.id === 'gps-setup' && (
                                            <div>
                                                {selectedArticle.content.setup.map((step, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">
                                                            Step {index + 1}: {step.step}
                                                        </h3>
                                                        <p className="text-gray-700 mb-4">{step.details}</p>
                                                        <div className="bg-green-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-green-800 mb-2">💡 Pro Tips:</h4>
                                                            <ul className="text-green-700">
                                                                {step.tips.map((tip, tipIndex) => (
                                                                    <li key={tipIndex}>• {tip}</li>
                                                                ))}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Mobile App Usage Article */}
                                        {selectedArticle.id === 'mobile-app-usage' && (
                                            <div>
                                                {selectedArticle.content.features.map((feature, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{feature.feature}</h3>
                                                        <ol className="space-y-3">
                                                            {feature.steps.map((step, stepIndex) => (
                                                                <li key={stepIndex} className="flex items-start">
                                                                    <span className="bg-blue-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm mr-3 mt-1 flex-shrink-0">
                                                                        {stepIndex + 1}
                                                                    </span>
                                                                    <span className="text-gray-700">{step}</span>
                                                                </li>
                                                            ))}
                                                        </ol>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Payroll Setup Article */}
                                        {selectedArticle.id === 'payroll-setup' && (
                                            <div>
                                                {selectedArticle.content.components.map((component, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{component.component}</h3>
                                                        <p className="text-gray-700 mb-4">{component.details}</p>
                                                        <div className="bg-purple-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-purple-800 mb-2">Components:</h4>
                                                            <ul className="space-y-1">
                                                                {component.elements.map((element, elementIndex) => (
                                                                    <li key={elementIndex} className="text-purple-700">• {element}</li>
                                                                ))}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Indian Compliance Article */}
                                        {selectedArticle.id === 'indian-compliance' && (
                                            <div>
                                                {selectedArticle.content.compliance.map((law, index) => (
                                                    <div key={index} className="mb-8 border-l-4 border-orange-500 pl-6">
                                                        <h3 className="text-2xl font-semibold mb-2">{law.law}</h3>
                                                        <p className="text-gray-600 mb-3">{law.details}</p>
                                                        <div className="bg-orange-50 p-4 rounded-lg mb-4">
                                                            <h4 className="font-semibold text-orange-800 mb-2">Calculation:</h4>
                                                            <p className="text-orange-700">{law.calculation}</p>
                                                        </div>
                                                        <div className="bg-gray-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold mb-2">Requirements:</h4>
                                                            <ul className="grid md:grid-cols-2 gap-2">
                                                                {law.requirements.map((req, reqIndex) => (
                                                                    <li key={reqIndex} className="text-sm bg-white px-3 py-1 rounded border">
                                                                        {req}
                                                                    </li>
                                                                ))}
                                                            </ul>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Visitor Registration Article */}
                                        {selectedArticle.id === 'visitor-registration' && (
                                            <div>
                                                {selectedArticle.content.process.map((stage, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{stage.stage}</h3>
                                                        <p className="text-gray-700 mb-4">{stage.details}</p>
                                                        <div className="bg-indigo-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-indigo-800 mb-2">Key Features:</h4>
                                                            <div className="grid md:grid-cols-2 gap-2">
                                                                {stage.features.map((feature, featureIndex) => (
                                                                    <div key={featureIndex} className="flex items-center text-indigo-700">
                                                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                        {feature}
                                                                    </div>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* API Overview Article */}
                                        {selectedArticle.id === 'api-overview' && (
                                            <div>
                                                {selectedArticle.content.features.map((feature, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{feature.category}</h3>
                                                        <p className="text-gray-700 mb-4">{feature.description}</p>
                                                        <div className="bg-gray-900 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-gray-200 mb-2">Available Endpoints:</h4>
                                                            <div className="space-y-1">
                                                                {feature.endpoints.map((endpoint, endpointIndex) => (
                                                                    <code key={endpointIndex} className="block text-green-400 text-sm bg-gray-800 px-2 py-1 rounded">
                                                                        {endpoint}
                                                                    </code>
                                                                ))}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* API Authentication Article */}
                                        {selectedArticle.id === 'api-authentication' && (
                                            <div>
                                                {selectedArticle.content.methods.map((method, index) => (
                                                    <div key={index} className="mb-8">
                                                        <h3 className="text-2xl font-semibold mb-4">{method.method}</h3>
                                                        <p className="text-gray-700 mb-4">{method.description}</p>
                                                        <div className="bg-blue-50 p-4 rounded-lg mb-4">
                                                            <h4 className="font-semibold text-blue-800 mb-2">Implementation:</h4>
                                                            <code className="block text-blue-700 bg-white p-2 rounded border">
                                                                {method.implementation}
                                                            </code>
                                                        </div>
                                                        <div className="bg-red-50 p-4 rounded-lg">
                                                            <h4 className="font-semibold text-red-800 mb-2">🔒 Security Note:</h4>
                                                            <p className="text-red-700">{method.security}</p>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        )}

                                        {/* Generic content fallback for articles without specific content */}
                                        {!['quick-start', 'system-requirements', 'account-setup', 'initial-configuration', 'adding-employees', 'role-management', 'gps-setup', 'mobile-app-usage', 'payroll-setup', 'indian-compliance', 'visitor-registration', 'api-overview', 'api-authentication'].includes(selectedArticle.id) && (
                                            <div className="bg-yellow-50 border border-yellow-200 p-6 rounded-lg">
                                                <h3 className="text-xl font-semibold text-yellow-800 mb-2">📝 Content Coming Soon</h3>
                                                <p className="text-yellow-700">
                                                    Detailed documentation for "{selectedArticle.title}" is currently being prepared. 
                                                    Please check back soon or contact our support team for immediate assistance.
                                                </p>
                                                <div className="mt-4">
                                                    <a href="mailto:support@secureserve.com" className="inline-flex items-center text-yellow-800 hover:text-yellow-900">
                                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                        </svg>
                                                        Contact Support
                                                    </a>
                                                </div>
                                            </div>
                                        )}

                                    </div>
                                </div>
                            ) : (
                                // Section Overview
                                <>
                                    {currentSection && (
                                        <>
                                            <div className="mb-8">
                                                <div className="flex items-center mb-4">
                                                    <span className="text-3xl mr-4">{currentSection.icon}</span>
                                                    <h2 className="text-3xl font-bold">{currentSection.title}</h2>
                                                </div>
                                                <p className="text-gray-600">
                                                    Comprehensive guides and tutorials for {currentSection.title.toLowerCase()}.
                                                </p>
                                            </div>

                                            <div className="grid md:grid-cols-2 gap-6">
                                                {currentSection.articles.map((article, index) => (
                                                    <div key={index} className="bg-white border rounded-xl p-6 hover:shadow-md transition-shadow cursor-pointer" 
                                                         onClick={() => setSelectedArticle(article)}>
                                                        <h3 className="text-xl font-semibold mb-3">{article.title}</h3>
                                                        <p className="text-gray-600 mb-4">{article.description}</p>
                                                        <div className="flex items-center justify-between">
                                                            <button className="text-blue-600 hover:text-blue-700 font-medium">
                                                                Read Article →
                                                            </button>
                                                            <div className="flex items-center text-sm text-gray-500">
                                                                <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                {article.readTime || '5 min read'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            {/* Developer Resources */}
            <section className="bg-gray-900 py-16">
                <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <h2 className="text-3xl font-bold text-white text-center mb-8">Developer Resources</h2>
                    <div className="grid md:grid-cols-3 gap-8">
                        <div className="text-center">
                            <div className="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-2">API Reference</h3>
                            <p className="text-gray-300 mb-4">Complete API documentation with examples</p>
                            <button className="text-blue-400 hover:text-blue-300">View API Docs →</button>
                        </div>

                        <div className="text-center">
                            <div className="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-2">SDK Libraries</h3>
                            <p className="text-gray-300 mb-4">Libraries for PHP, Node.js, Python, and more</p>
                            <button className="text-green-400 hover:text-green-300">Download SDKs →</button>
                        </div>

                        <div className="text-center">
                            <div className="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg className="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-2">Webhooks</h3>
                            <p className="text-gray-300 mb-4">Real-time notifications for your applications</p>
                            <button className="text-purple-400 hover:text-purple-300">Setup Webhooks →</button>
                        </div>
                    </div>
                </div>
            </section>

            {/* Support CTA */}
            <section className="bg-blue-600 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-4">
                        Need Additional Help?
                    </h2>
                    <p className="text-blue-100 mb-8">
                        Can't find what you're looking for? Our support team is here to help.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link 
                            href="/help-center"
                            className="bg-white text-blue-600 px-8 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                        >
                            Visit Help Center
                        </Link>
                        <a 
                            href="mailto:support@secureserve.com"
                            className="border-2 border-white text-white px-8 py-3 rounded-lg font-medium hover:bg-white hover:text-blue-600 transition-colors"
                        >
                            Contact Support
                        </a>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}