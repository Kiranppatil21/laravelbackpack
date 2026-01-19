import React from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';

export default function Index({ jobOpenings, departments, locations }) {
    const { delete: destroy } = useForm();

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this job opening?')) {
            destroy(route('admin.job-openings.destroy', id));
        }
    };

    const statusColors = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800',
        draft: 'bg-yellow-100 text-yellow-800'
    };

    const priorityColors = {
        1: 'bg-blue-100 text-blue-800',
        2: 'bg-orange-100 text-orange-800',
        3: 'bg-red-100 text-red-800'
    };

    const priorityLabels = {
        1: 'Normal',
        2: 'High', 
        3: 'Urgent'
    };

    return (
        <AdminLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Job Openings Management</h2>}
        >
            <Head title="Job Openings" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            <div className="flex justify-between items-center mb-6">
                                <h1 className="text-2xl font-bold">Job Openings</h1>
                                <a 
                                    href={route('admin.job-openings.create')}
                                    className="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    Create New Job Opening
                                </a>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full table-auto">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {jobOpenings.length > 0 ? (
                                            jobOpenings.map((job) => (
                                                <tr key={job.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div>
                                                            <div className="text-sm font-medium text-gray-900">{job.title}</div>
                                                            <div className="text-sm text-gray-500">{job.experience_level}</div>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {job.department}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {job.location}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {job.type}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`px-2 py-1 text-xs rounded-full ${statusColors[job.status]}`}>
                                                            {job.status}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <span className={`px-2 py-1 text-xs rounded-full ${priorityColors[job.priority]}`}>
                                                            {priorityLabels[job.priority]}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div className="flex space-x-2">
                                                            <a
                                                                href={route('admin.job-openings.edit', job.id)}
                                                                className="text-blue-600 hover:text-blue-900"
                                                            >
                                                                Edit
                                                            </a>
                                                            <button
                                                                onClick={() => handleDelete(job.id)}
                                                                className="text-red-600 hover:text-red-900"
                                                            >
                                                                Delete
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-12 text-center text-gray-500">
                                                    <div className="text-6xl mb-4">💼</div>
                                                    <div className="text-lg font-medium mb-2">No Job Openings Yet</div>
                                                    <div className="text-sm">Create your first job opening to get started.</div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}