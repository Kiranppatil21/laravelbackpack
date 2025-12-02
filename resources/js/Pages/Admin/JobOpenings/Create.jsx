import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        department: '',
        location: '',
        type: 'full-time',
        experience_level: '',
        description: '',
        requirements: [],
        salary_range: '',
        status: 'active',
        contact_email: '',
        priority: 1,
        application_deadline: ''
    });

    const [requirementInput, setRequirementInput] = useState('');

    const departmentOptions = [
        'Engineering',
        'Design',
        'Product',
        'Sales',
        'Marketing',
        'Customer Success',
        'Operations',
        'HR',
        'Finance'
    ];

    const locationOptions = [
        'Mumbai',
        'Bangalore',
        'Delhi',
        'Pune',
        'Hyderabad',
        'Chennai',
        'Kolkata',
        'Remote',
        'Hybrid'
    ];

    const addRequirement = () => {
        if (requirementInput.trim() && !data.requirements.includes(requirementInput.trim())) {
            setData('requirements', [...data.requirements, requirementInput.trim()]);
            setRequirementInput('');
        }
    };

    const removeRequirement = (index) => {
        const newRequirements = data.requirements.filter((_, i) => i !== index);
        setData('requirements', newRequirements);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.job-openings.store'));
    };

    return (
        <AdminLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Create Job Opening</h2>}
        >
            <Head title="Create Job Opening" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h1 className="text-2xl font-bold">Create New Job Opening</h1>
                                <a 
                                    href={route('admin.job-openings.index')}
                                    className="text-gray-600 hover:text-gray-800"
                                >
                                    ← Back to List
                                </a>
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Title */}
                                    <div className="md:col-span-2">
                                        <label htmlFor="title" className="block text-sm font-medium text-gray-700 mb-2">
                                            Job Title *
                                        </label>
                                        <input
                                            type="text"
                                            id="title"
                                            value={data.title}
                                            onChange={(e) => setData('title', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required
                                        />
                                        {errors.title && <div className="text-red-600 text-sm mt-1">{errors.title}</div>}
                                    </div>

                                    {/* Department */}
                                    <div>
                                        <label htmlFor="department" className="block text-sm font-medium text-gray-700 mb-2">
                                            Department *
                                        </label>
                                        <select
                                            id="department"
                                            value={data.department}
                                            onChange={(e) => setData('department', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required
                                        >
                                            <option value="">Select Department</option>
                                            {departmentOptions.map((dept) => (
                                                <option key={dept} value={dept}>{dept}</option>
                                            ))}
                                        </select>
                                        {errors.department && <div className="text-red-600 text-sm mt-1">{errors.department}</div>}
                                    </div>

                                    {/* Location */}
                                    <div>
                                        <label htmlFor="location" className="block text-sm font-medium text-gray-700 mb-2">
                                            Location *
                                        </label>
                                        <select
                                            id="location"
                                            value={data.location}
                                            onChange={(e) => setData('location', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required
                                        >
                                            <option value="">Select Location</option>
                                            {locationOptions.map((loc) => (
                                                <option key={loc} value={loc}>{loc}</option>
                                            ))}
                                        </select>
                                        {errors.location && <div className="text-red-600 text-sm mt-1">{errors.location}</div>}
                                    </div>

                                    {/* Type */}
                                    <div>
                                        <label htmlFor="type" className="block text-sm font-medium text-gray-700 mb-2">
                                            Employment Type *
                                        </label>
                                        <select
                                            id="type"
                                            value={data.type}
                                            onChange={(e) => setData('type', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required
                                        >
                                            <option value="full-time">Full-time</option>
                                            <option value="part-time">Part-time</option>
                                            <option value="contract">Contract</option>
                                            <option value="internship">Internship</option>
                                        </select>
                                        {errors.type && <div className="text-red-600 text-sm mt-1">{errors.type}</div>}
                                    </div>

                                    {/* Experience Level */}
                                    <div>
                                        <label htmlFor="experience_level" className="block text-sm font-medium text-gray-700 mb-2">
                                            Experience Level
                                        </label>
                                        <input
                                            type="text"
                                            id="experience_level"
                                            value={data.experience_level}
                                            onChange={(e) => setData('experience_level', e.target.value)}
                                            placeholder="e.g., 2-4 years"
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        {errors.experience_level && <div className="text-red-600 text-sm mt-1">{errors.experience_level}</div>}
                                    </div>

                                        {/* Status */}
                                        <div>
                                            <label htmlFor="status" className="block text-sm font-medium text-gray-700 mb-2">
                                                Status *
                                            </label>
                                            <select
                                                id="status"
                                                value={data.status}
                                                onChange={(e) => setData('status', e.target.value)}
                                                className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                required
                                            >
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="filled">Filled</option>
                                            </select>
                                            {errors.status && <div className="text-red-600 text-sm mt-1">{errors.status}</div>}
                                        </div>                                    {/* Priority */}
                                    <div>
                                        <label htmlFor="priority" className="block text-sm font-medium text-gray-700 mb-2">
                                            Priority
                                        </label>
                                        <select
                                            id="priority"
                                            value={data.priority}
                                            onChange={(e) => setData('priority', parseInt(e.target.value))}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                            <option value={1}>Normal</option>
                                            <option value={2}>High</option>
                                            <option value={3}>Urgent</option>
                                        </select>
                                        {errors.priority && <div className="text-red-600 text-sm mt-1">{errors.priority}</div>}
                                    </div>

                                    {/* Salary Range */}
                                    <div>
                                        <label htmlFor="salary_range" className="block text-sm font-medium text-gray-700 mb-2">
                                            Salary Range
                                        </label>
                                        <input
                                            type="text"
                                            id="salary_range"
                                            value={data.salary_range}
                                            onChange={(e) => setData('salary_range', e.target.value)}
                                            placeholder="e.g., ₹8-12 LPA"
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        {errors.salary_range && <div className="text-red-600 text-sm mt-1">{errors.salary_range}</div>}
                                    </div>

                                    {/* Contact Email */}
                                    <div>
                                        <label htmlFor="contact_email" className="block text-sm font-medium text-gray-700 mb-2">
                                            Contact Email
                                        </label>
                                        <input
                                            type="email"
                                            id="contact_email"
                                            value={data.contact_email}
                                            onChange={(e) => setData('contact_email', e.target.value)}
                                            placeholder="hr@company.com"
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        {errors.contact_email && <div className="text-red-600 text-sm mt-1">{errors.contact_email}</div>}
                                    </div>

                                    {/* Application Deadline */}
                                    <div>
                                        <label htmlFor="application_deadline" className="block text-sm font-medium text-gray-700 mb-2">
                                            Application Deadline
                                        </label>
                                        <input
                                            type="date"
                                            id="application_deadline"
                                            value={data.application_deadline}
                                            onChange={(e) => setData('application_deadline', e.target.value)}
                                            className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        />
                                        {errors.application_deadline && <div className="text-red-600 text-sm mt-1">{errors.application_deadline}</div>}
                                    </div>
                                </div>

                                {/* Description */}
                                <div>
                                    <label htmlFor="description" className="block text-sm font-medium text-gray-700 mb-2">
                                        Job Description *
                                    </label>
                                    <textarea
                                        id="description"
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        rows={6}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    />
                                    {errors.description && <div className="text-red-600 text-sm mt-1">{errors.description}</div>}
                                </div>

                                {/* Requirements */}
                                <div>
                                    <label htmlFor="requirements" className="block text-sm font-medium text-gray-700 mb-2">
                                        Requirements
                                    </label>
                                    <div className="flex gap-2 mb-3">
                                        <input
                                            type="text"
                                            value={requirementInput}
                                            onChange={(e) => setRequirementInput(e.target.value)}
                                            placeholder="Add a requirement"
                                            className="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            onKeyPress={(e) => e.key === 'Enter' && (e.preventDefault(), addRequirement())}
                                        />
                                        <button
                                            type="button"
                                            onClick={addRequirement}
                                            className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors"
                                        >
                                            Add
                                        </button>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {data.requirements.map((req, index) => (
                                            <span key={index} className="bg-gray-100 text-gray-700 px-3 py-1 rounded-md text-sm flex items-center gap-2">
                                                {req}
                                                <button
                                                    type="button"
                                                    onClick={() => removeRequirement(index)}
                                                    className="text-red-600 hover:text-red-800"
                                                >
                                                    ×
                                                </button>
                                            </span>
                                        ))}
                                    </div>
                                    {errors.requirements && <div className="text-red-600 text-sm mt-1">{errors.requirements}</div>}
                                </div>

                                {/* Submit Button */}
                                <div className="flex justify-end gap-4">
                                    <a
                                        href={route('admin.job-openings.index')}
                                        className="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors"
                                    >
                                        Cancel
                                    </a>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors disabled:opacity-50"
                                    >
                                        {processing ? 'Creating...' : 'Create Job Opening'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}