import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import MarketingLayout from '@/Components/MarketingLayout';

export default function Careers({ jobOpenings = [], departments = [], locations = [] }) {
    const [selectedDepartment, setSelectedDepartment] = useState('all');
    const [selectedLocation, setSelectedLocation] = useState('all');

    const jsonLd = {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "SecureServe",
        "url": "https://secureserve.com",
        "logo": "https://secureserve.com/logo.png",
        "jobPosting": jobOpenings.map(job => ({
            "@type": "JobPosting",
            "title": job.title,
            "description": job.description,
            "datePosted": job.created_at,
            "employmentType": job.type,
            "hiringOrganization": {
                "@type": "Organization",
                "name": "SecureServe"
            },
            "jobLocation": {
                "@type": "Place",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": job.location,
                    "addressCountry": "IN"
                }
            }
        }))
    };

    const benefits = [
        {
            icon: "💰",
            title: "Competitive Salary",
            description: "Market-leading compensation packages with performance bonuses and stock options."
        },
        {
            icon: "🏥",
            title: "Health & Wellness",
            description: "Comprehensive health insurance, mental health support, and wellness programs."
        },
        {
            icon: "🎓",
            title: "Learning & Development",
            description: "Continuous learning opportunities, conferences, certifications, and skill development programs."
        },
        {
            icon: "🏠",
            title: "Work-Life Balance",
            description: "Flexible working hours, remote work options, and unlimited PTO policy."
        },
        {
            icon: "🚀",
            title: "Career Growth",
            description: "Clear career progression paths and leadership development opportunities."
        },
        {
            icon: "🌍",
            title: "Impact",
            description: "Work on products that make a real difference in India's security landscape."
        }
    ];

    const companyValues = [
        {
            icon: "🎯",
            title: "Innovation First",
            description: "We push boundaries and embrace new technologies to solve complex problems."
        },
        {
            icon: "👥",
            title: "Team Collaboration",
            description: "We believe in the power of diverse teams working together towards common goals."
        },
        {
            icon: "💡",
            title: "Continuous Learning",
            description: "We invest in our people's growth and encourage experimentation and learning."
        },
        {
            icon: "🏆",
            title: "Excellence",
            description: "We strive for excellence in everything we do and take pride in our work."
        }
    ];

    // Convert departments and locations arrays to the format expected by the component
    const departmentOptions = ['All Departments', ...departments].map((dept, index) => ({
        value: index === 0 ? 'all' : dept,
        label: dept
    }));

    const locationOptions = ['All Locations', ...locations].map((loc, index) => ({
        value: index === 0 ? 'all' : loc,
        label: loc
    }));

    // Filter job openings based on selected filters
    const filteredJobs = jobOpenings.filter(job => {
        const departmentMatch = selectedDepartment === 'all' || job.department === selectedDepartment;
        const locationMatch = selectedLocation === 'all' || job.location === selectedLocation;
        return departmentMatch && locationMatch;
    });

    return (
        <MarketingLayout
            title="Careers - Join SecureServe Security Management Team"
            description="Join SecureServe and help revolutionize India's security industry. Explore exciting career opportunities in engineering, design, product, sales, and more."
            keywords="SecureServe careers, security tech jobs, software developer jobs Mumbai, product manager jobs, UI UX designer jobs India"
            canonical="https://secureserve.com/careers"
            ogType="website"
            jsonLd={jsonLd}
        >
            {/* Hero Section */}
            <section className="relative overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white">
                <div className="absolute inset-0 bg-black opacity-20"></div>
                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                    <div className="text-center">
                        <h1 className="text-4xl md:text-6xl font-bold mb-6 leading-tight">
                            Build the Future of 
                            <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-300 to-indigo-300"> Security Tech</span>
                        </h1>
                        <p className="text-xl md:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto leading-relaxed">
                            Join SecureServe and help revolutionize how security agencies across India manage their operations, 
                            employees, and client relationships.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 justify-center">
                            <a 
                                href="#openings" 
                                className="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-4 rounded-lg transition-colors text-lg shadow-lg"
                            >
                                View Open Positions
                            </a>
                            <a 
                                href="#culture" 
                                className="border-2 border-blue-400 hover:bg-blue-400 hover:text-blue-900 text-blue-100 font-semibold px-8 py-4 rounded-lg transition-colors text-lg"
                            >
                                Learn About Our Culture
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {/* Why Join Us Section */}
            <section id="culture" className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Why Work at SecureServe?
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Join a company that's making a real impact on India's security industry
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        {benefits.map((benefit, index) => (
                            <div key={index} className="bg-white p-6 rounded-xl shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
                                <div className="text-4xl mb-4">{benefit.icon}</div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">{benefit.title}</h3>
                                <p className="text-gray-600 leading-relaxed">{benefit.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Company Values Section */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Our Values
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            The principles that guide our team and shape our culture
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        {companyValues.map((value, index) => (
                            <div key={index} className="bg-white p-6 rounded-xl shadow-lg text-center hover:shadow-xl transition-shadow">
                                <div className="text-4xl mb-4">{value.icon}</div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">{value.title}</h3>
                                <p className="text-gray-600 leading-relaxed">{value.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Job Openings Section */}
            <section id="openings" className="py-20 bg-white">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Current Openings
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Find your perfect role and start your journey with SecureServe
                        </p>
                    </div>

                    {/* Filters */}
                    <div className="flex flex-wrap justify-center gap-4 mb-12">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Department</label>
                            <select 
                                className="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value={selectedDepartment}
                                onChange={(e) => setSelectedDepartment(e.target.value)}
                            >
                                {departmentOptions.map(option => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <select 
                                className="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                value={selectedLocation}
                                onChange={(e) => setSelectedLocation(e.target.value)}
                            >
                                {locationOptions.map(option => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Job Listings */}
                    {filteredJobs.length > 0 ? (
                        <div className="space-y-6">
                            {filteredJobs.map((job) => (
                                <div key={job.id} className="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
                                    <div className="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                                        <div className="flex-1">
                                            <div className="flex flex-wrap items-center gap-4 mb-3">
                                                <h3 className="text-xl font-bold text-gray-900">{job.title}</h3>
                                                <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                                    {job.department}
                                                </span>
                                                <span className="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                                    {job.type}
                                                </span>
                                                {job.priority_label === 'urgent' && (
                                                    <span className="bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm font-medium">
                                                        Urgent
                                                    </span>
                                                )}
                                            </div>
                                            
                                            <div className="flex flex-wrap items-center gap-6 text-gray-600 mb-4">
                                                <div className="flex items-center">
                                                    <span className="text-lg mr-2">📍</span>
                                                    <span>{job.location}</span>
                                                </div>
                                                {job.experience_level && (
                                                    <div className="flex items-center">
                                                        <span className="text-lg mr-2">💼</span>
                                                        <span>{job.experience_level}</span>
                                                    </div>
                                                )}
                                                <div className="flex items-center">
                                                    <span className="text-lg mr-2">📅</span>
                                                    <span>Posted {job.posted_ago}</span>
                                                </div>
                                                {job.application_deadline && new Date(job.application_deadline) > new Date() && (
                                                    <div className="flex items-center">
                                                        <span className="text-lg mr-2">⏰</span>
                                                        <span>Deadline: {new Date(job.application_deadline).toLocaleDateString()}</span>
                                                    </div>
                                                )}
                                            </div>
                                            
                                            <p className="text-gray-700 mb-4">{job.description}</p>
                                            
                                            {job.salary_range && (
                                                <div className="mb-4">
                                                    <span className="text-sm font-medium text-gray-900">Salary Range: </span>
                                                    <span className="text-sm text-gray-600">{job.salary_range}</span>
                                                </div>
                                            )}
                                            
                                            {job.requirements && job.requirements.length > 0 && (
                                                <div className="flex flex-wrap gap-2 mb-4">
                                                    {job.requirements.map((req, index) => (
                                                        <span key={index} className="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-sm">
                                                            {req}
                                                        </span>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                        
                                        <div className="lg:ml-6 mt-4 lg:mt-0">
                                            <a
                                                href={`mailto:${job.contact_email || 'careers@rajsecurity.in'}?subject=Application for ${job.title}&body=Dear Hiring Team,%0A%0AI am interested in applying for the ${job.title} position at SecureServe.%0A%0AThank you for your consideration.%0A%0ABest regards`}
                                                className="w-full lg:w-auto bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors inline-block text-center"
                                            >
                                                Apply Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-16 bg-white border border-gray-200 rounded-xl">
                            <div className="text-6xl mb-4">💼</div>
                            <h3 className="text-xl font-bold text-gray-900 mb-2">No Open Positions</h3>
                            <p className="text-gray-600 mb-6">
                                We don't have any openings that match your current filters right now.
                            </p>
                            <p className="text-gray-500">
                                Try adjusting your search criteria or check back later for new opportunities.
                            </p>
                        </div>
                    )}
                </div>
            </section>

            {/* Application Process Section */}
            <section className="py-20 bg-gray-50">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="text-center mb-16">
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Our Hiring Process
                        </h2>
                        <p className="text-xl text-gray-600 max-w-2xl mx-auto">
                            Simple, transparent, and designed to get to know you better
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
                        {[
                            {
                                step: "01",
                                title: "Application",
                                description: "Submit your application with resume and cover letter",
                                icon: "📄"
                            },
                            {
                                step: "02", 
                                title: "Phone Screening",
                                description: "Initial conversation with our HR team",
                                icon: "📞"
                            },
                            {
                                step: "03",
                                title: "Technical Interview",
                                description: "Technical assessment with team members",
                                icon: "💻"
                            },
                            {
                                step: "04",
                                title: "Final Round",
                                description: "Meet the team and culture fit discussion",
                                icon: "🤝"
                            }
                        ].map((step, index) => (
                            <div key={index} className="text-center">
                                <div className="bg-blue-600 text-white w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg mx-auto mb-4">
                                    {step.step}
                                </div>
                                <div className="text-4xl mb-4">{step.icon}</div>
                                <h3 className="text-xl font-bold text-gray-900 mb-3">{step.title}</h3>
                                <p className="text-gray-600">{step.description}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                <div className="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                    <h2 className="text-3xl md:text-4xl font-bold mb-6">
                        Ready to Join Our Mission?
                    </h2>
                    <p className="text-xl text-blue-100 mb-8">
                        Don't see the right role? We're always looking for talented individuals to join our team.
                    </p>
                    <div className="space-y-4 sm:space-y-0 sm:space-x-4 sm:flex sm:justify-center">
                        <a
                            href="mailto:careers@rajsecurity.in"
                            className="inline-block bg-white text-blue-600 font-semibold px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors shadow-lg"
                        >
                            Send Your Resume
                        </a>
                        <a
                            href="/about-us"
                            className="inline-block border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition-colors"
                        >
                            Learn More About Us
                        </a>
                    </div>
                    
                    <div className="mt-12 pt-8 border-t border-blue-500">
                        <h3 className="text-lg font-semibold mb-4">Connect With Us</h3>
                        <div className="flex justify-center space-x-6">
                            <a href="mailto:careers@rajsecurity.in" className="text-blue-200 hover:text-white transition-colors">
                                📧 careers@rajsecurity.in
                            </a>
                            <a href="tel:+918888888888" className="text-blue-200 hover:text-white transition-colors">
                                📞 +91 88888 88888
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
}