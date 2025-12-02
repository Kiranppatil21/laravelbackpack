import React from 'react';
import MarketingLayout from '@/Components/MarketingLayout.jsx';

export default function PrivacyPolicy() {
    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Privacy Policy - SecureServe",
        "description": "Privacy Policy for SecureServe security management platform. Learn how we collect, use, and protect your personal information.",
        "url": window.location.href,
        "datePublished": "2025-01-01",
        "dateModified": "2025-11-10"
    };

    return (
        <MarketingLayout 
            title="Privacy Policy - How We Protect Your Data | SecureServe"
            description="SecureServe Privacy Policy: Learn how we collect, use, and protect your personal information. Transparent data practices for security management platform."
            keywords="SecureServe privacy policy, data protection, personal information, privacy rights, GDPR compliance, data security"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="bg-gradient-to-br from-blue-600 to-indigo-700 py-16">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h1 className="text-4xl md:text-5xl font-bold text-white mb-6">
                        Privacy Policy
                    </h1>
                    <p className="text-xl text-blue-100 mb-4">
                        Your privacy is important to us
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
                            <h2 className="text-3xl font-bold mb-6">Introduction</h2>
                            <p className="text-gray-600 mb-4">
                                SecureServe ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our security management platform, including our website, mobile applications, and related services (collectively, the "Services").
                            </p>
                            <p className="text-gray-600">
                                Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access or use our Services.
                            </p>
                        </div>

                        {/* Information We Collect */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Information We Collect</h2>
                            
                            <h3 className="text-xl font-semibold mb-4">Personal Information</h3>
                            <p className="text-gray-600 mb-4">We may collect personal information that you provide to us, including:</p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Name, email address, and phone number</li>
                                <li>• Company information and business address</li>
                                <li>• Employee details (names, photos, identification documents)</li>
                                <li>• Payment and billing information</li>
                                <li>• Location data for GPS attendance tracking</li>
                                <li>• Photos and documents uploaded to the platform</li>
                            </ul>

                            <h3 className="text-xl font-semibold mb-4">Automatically Collected Information</h3>
                            <p className="text-gray-600 mb-4">When you access our Services, we may automatically collect:</p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• IP address and device information</li>
                                <li>• Browser type and operating system</li>
                                <li>• Usage patterns and interaction data</li>
                                <li>• Cookies and similar tracking technologies</li>
                                <li>• GPS location data (with your consent)</li>
                            </ul>
                        </div>

                        {/* How We Use Information */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">How We Use Your Information</h2>
                            <p className="text-gray-600 mb-4">We use the information we collect to:</p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Provide, maintain, and improve our Services</li>
                                <li>• Process payments and manage your account</li>
                                <li>• Track employee attendance and manage payroll</li>
                                <li>• Communicate with you about your account and our Services</li>
                                <li>• Provide customer support and technical assistance</li>
                                <li>• Comply with legal obligations and regulatory requirements</li>
                                <li>• Detect, prevent, and address technical issues or security threats</li>
                                <li>• Generate analytics and insights to improve our platform</li>
                            </ul>
                        </div>

                        {/* Information Sharing */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Information Sharing and Disclosure</h2>
                            <p className="text-gray-600 mb-4">We do not sell, trade, or otherwise transfer your personal information to third parties except as described below:</p>
                            
                            <h3 className="text-xl font-semibold mb-4">Service Providers</h3>
                            <p className="text-gray-600 mb-4">
                                We may share information with trusted third-party service providers who assist us in operating our platform, including payment processors, cloud hosting providers, and analytics services.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Legal Requirements</h3>
                            <p className="text-gray-600 mb-4">
                                We may disclose your information if required by law, court order, or other legal processes, or if we believe disclosure is necessary to protect our rights, property, or safety.
                            </p>

                            <h3 className="text-xl font-semibold mb-4">Business Transfers</h3>
                            <p className="text-gray-600 mb-6">
                                In the event of a merger, acquisition, or sale of assets, your information may be transferred to the new entity.
                            </p>
                        </div>

                        {/* Data Security */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Data Security</h2>
                            <p className="text-gray-600 mb-4">
                                We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• End-to-end encryption for data transmission</li>
                                <li>• Secure data storage with regular backups</li>
                                <li>• Multi-factor authentication and access controls</li>
                                <li>• Regular security audits and vulnerability assessments</li>
                                <li>• Employee training on data protection best practices</li>
                            </ul>
                            <p className="text-gray-600">
                                However, no method of transmission over the internet or electronic storage is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.
                            </p>
                        </div>

                        {/* Your Rights */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Your Privacy Rights</h2>
                            <p className="text-gray-600 mb-4">Depending on your location, you may have certain rights regarding your personal information:</p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• <strong>Access:</strong> Request access to your personal information</li>
                                <li>• <strong>Correction:</strong> Request correction of inaccurate information</li>
                                <li>• <strong>Deletion:</strong> Request deletion of your personal information</li>
                                <li>• <strong>Portability:</strong> Request a copy of your information in a portable format</li>
                                <li>• <strong>Restriction:</strong> Request restriction of processing</li>
                                <li>• <strong>Objection:</strong> Object to certain types of processing</li>
                                <li>• <strong>Withdrawal:</strong> Withdraw consent where processing is based on consent</li>
                            </ul>
                            <p className="text-gray-600">
                                To exercise these rights, please contact us at <a href="mailto:privacy@secureserve.com" className="text-blue-600 hover:text-blue-700">privacy@secureserve.com</a>.
                            </p>
                        </div>

                        {/* Data Retention */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Data Retention</h2>
                            <p className="text-gray-600 mb-4">
                                We retain your personal information only as long as necessary to fulfill the purposes for which it was collected, including:
                            </p>
                            <ul className="text-gray-600 mb-6 space-y-2">
                                <li>• Providing our Services to you</li>
                                <li>• Complying with legal, regulatory, or contractual obligations</li>
                                <li>• Resolving disputes and enforcing agreements</li>
                                <li>• Maintaining business records for legitimate business purposes</li>
                            </ul>
                        </div>

                        {/* International Transfers */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">International Data Transfers</h2>
                            <p className="text-gray-600 mb-4">
                                Your information may be transferred to and processed in countries other than your own. We ensure that such transfers are conducted in accordance with applicable data protection laws and include appropriate safeguards to protect your information.
                            </p>
                        </div>

                        {/* Children's Privacy */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Children's Privacy</h2>
                            <p className="text-gray-600 mb-4">
                                Our Services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children under 18. If we become aware that we have collected personal information from a child under 18, we will take steps to delete such information.
                            </p>
                        </div>

                        {/* Cookies */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Cookies and Tracking Technologies</h2>
                            <p className="text-gray-600 mb-4">
                                We use cookies and similar tracking technologies to enhance your experience on our platform. You can control cookie settings through your browser preferences. However, disabling cookies may affect the functionality of our Services.
                            </p>
                        </div>

                        {/* Updates */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Changes to This Privacy Policy</h2>
                            <p className="text-gray-600 mb-4">
                                We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date. We encourage you to review this Privacy Policy periodically.
                            </p>
                        </div>

                        {/* Contact */}
                        <div className="mb-12">
                            <h2 className="text-3xl font-bold mb-6">Contact Us</h2>
                            <p className="text-gray-600 mb-4">
                                If you have any questions about this Privacy Policy or our privacy practices, please contact us:
                            </p>
                            <div className="bg-gray-50 p-6 rounded-lg">
                                <p className="text-gray-600 mb-2"><strong>Email:</strong> privacy@secureserve.com</p>
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
                        Questions About Your Privacy?
                    </h2>
                    <p className="text-blue-100 mb-8">
                        We're committed to transparency and protecting your privacy rights.
                    </p>
                    <a 
                        href="mailto:privacy@secureserve.com"
                        className="bg-white text-blue-600 px-8 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                    >
                        Contact Privacy Team
                    </a>
                </div>
            </section>
        </MarketingLayout>
    );
}