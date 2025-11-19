import React from 'react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function TermsOfService() {
    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Terms of Service - SecureServe",
        "description": "Terms of Service for SecureServe security management platform. Legal terms and conditions for using our services.",
        "url": window.location.href,
        "datePublished": "2025-01-01",
        "dateModified": "2025-11-10"
    };

    return (
        <MarketingLayout 
            title="Terms of Service - Legal Terms & Conditions | SecureServe"
            description="SecureServe Terms of Service: Legal terms and conditions for using our security management platform. Service agreements, user responsibilities, and limitations."
            keywords="SecureServe terms of service, legal terms, service agreement, user agreement, terms and conditions, software license"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Terms of Service
                    </h1>
                    <p className="text-xl text-blue-100 mb-4">
                        Legal terms and conditions for using SecureServe
                    </p>
                    <p className="text-blue-200">
                        Last updated: November 10, 2025
                    </p>
                </div>
            </section>

            {/* Content */}
            <section className="py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="prose prose-lg max-w-none">
                        
                        {/* Introduction */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Agreement to Terms</h2>
                            <p className="text-gray-600 mb-4">
                                These Terms of Service ("Terms") constitute a legally binding agreement between you and SecureServe Technologies Pvt. Ltd. ("SecureServe," "we," "us," or "our") regarding your use of our security management platform, including our website, mobile applications, and related services (collectively, the "Services").
                            </p>
                            <p className="text-gray-600 mb-4">
                                By accessing or using our Services, you agree to be bound by these Terms. If you do not agree to these Terms, you may not access or use our Services.
                            </p>
                            <p className="text-gray-600">
                                We reserve the right to update these Terms at any time. Your continued use of the Services after any changes constitutes acceptance of the updated Terms.
                            </p>
                        </div>

                        {/* Eligibility */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Eligibility</h2>
                            <p className="text-gray-600 mb-4">
                                To use our Services, you must:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Be at least 18 years of age</li>
                                <li>• Have the legal authority to enter into this agreement</li>
                                <li>• Operate a legitimate security agency or related business</li>
                                <li>• Provide accurate and complete information during registration</li>
                                <li>• Comply with all applicable laws and regulations</li>
                            </ul>
                        </div>

                        {/* Account Registration */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Account Registration and Security</h2>
                            <p className="text-gray-600 mb-4">
                                To access certain features of our Services, you must create an account. You agree to:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Provide accurate, current, and complete information</li>
                                <li>• Maintain and update your account information</li>
                                <li>• Keep your password secure and confidential</li>
                                <li>• Notify us immediately of any unauthorized access</li>
                                <li>• Accept responsibility for all activities under your account</li>
                            </ul>
                            <p className="text-gray-600">
                                We reserve the right to suspend or terminate accounts that violate these Terms or are used for fraudulent or illegal activities.
                            </p>
                        </div>

                        {/* Acceptable Use */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Acceptable Use Policy</h2>
                            <p className="text-gray-600 mb-4">You agree not to use our Services to:</p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Violate any applicable laws, regulations, or third-party rights</li>
                                <li>• Upload, transmit, or distribute malicious software or harmful content</li>
                                <li>• Attempt to gain unauthorized access to our systems or other users' accounts</li>
                                <li>• Interfere with or disrupt the operation of our Services</li>
                                <li>• Use automated systems to access our Services without permission</li>
                                <li>• Reverse engineer, decompile, or attempt to extract source code</li>
                                <li>• Resell or redistribute our Services without authorization</li>
                                <li>• Collect personal information about other users without consent</li>
                            </ul>
                        </div>

                        {/* Service Features */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Service Features and Availability</h2>
                            <p className="text-gray-600 mb-4">
                                Our Services include, but are not limited to:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Employee management and scheduling</li>
                                <li>• GPS-based attendance tracking</li>
                                <li>• Payroll processing and compliance management</li>
                                <li>• Client management and invoicing</li>
                                <li>• Visitor management and security screening</li>
                                <li>• Reporting and analytics</li>
                            </ul>
                            <p className="text-gray-600 mb-4">
                                We strive to maintain high service availability but cannot guarantee uninterrupted access. We may temporarily suspend Services for maintenance, updates, or due to circumstances beyond our control.
                            </p>
                        </div>

                        {/* Payment Terms */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Payment Terms</h2>
                            <h3 className="text-xl font-semibold mb-4">Subscription Plans</h3>
                            <p className="text-gray-600 mb-4">
                                Our Services are offered under various subscription plans with different features and pricing. By selecting a plan, you agree to pay the applicable fees.
                            </p>
                            
                            <h3 className="text-xl font-semibold mb-4">Billing</h3>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Subscription fees are billed in advance on a monthly or annual basis</li>
                                <li>• All prices are in Indian Rupees (INR) and include applicable taxes</li>
                                <li>• Payment is due immediately upon invoice</li>
                                <li>• We accept major credit cards, UPI, and bank transfers</li>
                                <li>• Failed payments may result in service suspension</li>
                            </ul>

                            <h3 className="text-xl font-semibold mb-4">Refund Policy</h3>
                            <p className="text-gray-600 mb-4">
                                We offer a 14-day free trial for new customers. After the trial period, refunds are provided only in accordance with our refund policy available at <a href="mailto:billing@secureserve.com" className="text-blue-600 hover:text-blue-700">billing@secureserve.com</a>.
                            </p>
                        </div>

                        {/* Data and Privacy */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Data Ownership and Privacy</h2>
                            <h3 className="text-xl font-semibold mb-4">Your Data</h3>
                            <p className="text-gray-600 mb-4">
                                You retain ownership of all data you upload or input into our Services ("Customer Data"). You are responsible for the accuracy, quality, and legality of your Customer Data.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Our Rights</h3>
                            <p className="text-gray-600 mb-4">
                                You grant us a limited license to use your Customer Data solely to provide our Services. We may aggregate and anonymize data for analytical purposes, provided it cannot identify you or your organization.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Privacy</h3>
                            <p className="text-gray-600 mb-6">
                                Our collection and use of personal information is governed by our Privacy Policy, which is incorporated into these Terms by reference.
                            </p>
                        </div>

                        {/* Intellectual Property */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Intellectual Property Rights</h2>
                            <p className="text-gray-600 mb-4">
                                The Services, including all software, content, trademarks, and other intellectual property, are owned by SecureServe and protected by intellectual property laws. You are granted a limited, non-exclusive license to use the Services in accordance with these Terms.
                            </p>
                            <p className="text-gray-600 mb-6">
                                You may not copy, modify, distribute, sell, or lease any part of our Services, nor may you reverse engineer or attempt to extract the source code, unless expressly permitted by law.
                            </p>
                        </div>

                        {/* Third-Party Services */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Third-Party Services</h2>
                            <p className="text-gray-600 mb-4">
                                Our Services may integrate with or contain links to third-party services, applications, or websites. These third-party services are governed by their own terms and privacy policies. We are not responsible for the content, policies, or practices of third-party services.
                            </p>
                        </div>

                        {/* Disclaimers */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Disclaimers</h2>
                            <p className="text-gray-600 mb-4">
                                OUR SERVICES ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT.
                            </p>
                            <p className="text-gray-600 mb-6">
                                We do not warrant that our Services will be uninterrupted, error-free, or completely secure. You use our Services at your own risk.
                            </p>
                        </div>

                        {/* Limitation of Liability */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Limitation of Liability</h2>
                            <p className="text-gray-600 mb-4">
                                TO THE MAXIMUM EXTENT PERMITTED BY LAW, SECURESERVE SHALL NOT BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING BUT NOT LIMITED TO LOSS OF PROFITS, DATA, OR BUSINESS OPPORTUNITIES.
                            </p>
                            <p className="text-gray-600 mb-6">
                                OUR TOTAL LIABILITY TO YOU FOR ALL CLAIMS SHALL NOT EXCEED THE AMOUNT YOU PAID TO US IN THE TWELVE MONTHS PRECEDING THE CLAIM.
                            </p>
                        </div>

                        {/* Indemnification */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Indemnification</h2>
                            <p className="text-gray-600 mb-4">
                                You agree to indemnify and hold harmless SecureServe from any claims, damages, losses, or expenses (including attorney fees) arising from:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Your use of our Services</li>
                                <li>• Your violation of these Terms</li>
                                <li>• Your violation of any applicable laws or regulations</li>
                                <li>• Your Customer Data or its use</li>
                            </ul>
                        </div>

                        {/* Termination */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Termination</h2>
                            <p className="text-gray-600 mb-4">
                                You may terminate your account at any time by contacting our support team. We may terminate or suspend your account immediately if you violate these Terms or engage in fraudulent or illegal activities.
                            </p>
                            <p className="text-gray-600 mb-6">
                                Upon termination, your access to the Services will cease, and we may delete your account and data after a reasonable retention period.
                            </p>
                        </div>

                        {/* Governing Law */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Governing Law and Jurisdiction</h2>
                            <p className="text-gray-600 mb-4">
                                These Terms are governed by the laws of India, without regard to conflict of law principles. Any disputes arising from these Terms or your use of our Services shall be subject to the exclusive jurisdiction of the courts in Mumbai, Maharashtra, India.
                            </p>
                        </div>

                        {/* General Provisions */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">General Provisions</h2>
                            <h3 className="text-xl font-semibold mb-4">Entire Agreement</h3>
                            <p className="text-gray-600 mb-4">
                                These Terms, together with our Privacy Policy, constitute the entire agreement between you and SecureServe regarding the Services.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Severability</h3>
                            <p className="text-gray-600 mb-4">
                                If any provision of these Terms is found to be unenforceable, the remaining provisions will remain in full force and effect.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Force Majeure</h3>
                            <p className="text-gray-600 mb-6">
                                We are not liable for any failure to perform our obligations due to circumstances beyond our reasonable control, including natural disasters, war, terrorism, or government actions.
                            </p>
                        </div>

                        {/* Contact Information */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Contact Information</h2>
                            <p className="text-gray-600 mb-4">
                                If you have any questions about these Terms, please contact us:
                            </p>
                            <div className="bg-gray-50 p-6 rounded-lg">
                                <p className="text-gray-600 mb-2"><strong>Email:</strong> legal@secureserve.com</p>
                                <p className="text-gray-600 mb-2"><strong>Phone:</strong> +91-8000-123-456</p>
                                <p className="text-gray-600 mb-2"><strong>Address:</strong> SecureServe Technologies Pvt. Ltd.</p>
                                <p className="text-gray-600 mb-2">123 Business District, Mumbai, Maharashtra 400001, India</p>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            {/* Contact CTA */}
            <section className="bg-blue-600 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl font-bold text-white mb-4">
                        Questions About These Terms?
                    </h2>
                    <p className="text-blue-100 mb-8">
                        Our legal team is here to help clarify any questions you may have.
                    </p>
                    <a 
                        href="mailto:legal@secureserve.com"
                        className="bg-white text-blue-600 px-8 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                    >
                        Contact Legal Team
                    </a>
                </div>
            </section>
        </MarketingLayout>
    );
}