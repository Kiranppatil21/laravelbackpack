import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ClientIndex({ auth, clients }) {
    const { flash } = usePage().props;

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                        Clients
                    </h2>
                    <Link
                        href={route('client.create-custom')}
                        className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Add New Client
                    </Link>
                </div>
            }
        >
            <Head title="Clients" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {flash?.success && (
                        <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {flash.success}
                        </div>
                    )}
                    
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="mb-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Client List ({clients.total} total)
                                </h3>
                                
                                {clients.data.length === 0 ? (
                                    <div className="text-center py-8">
                                        <p className="text-gray-500 text-lg">No clients found.</p>
                                        <Link
                                            href={route('client.create-custom')}
                                            className="mt-4 inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Create Your First Client
                                        </Link>
                                    </div>
                                ) : (
                                    <div className="overflow-x-auto">
                                        <table className="min-w-full divide-y divide-gray-200">
                                            <thead className="bg-gray-50">
                                                <tr>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        ID
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Name
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Email
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Company
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Contacts
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Status
                                                    </th>
                                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Actions
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody className="bg-white divide-y divide-gray-200">
                                                {clients.data.map((client) => (
                                                    <tr key={client.id} className="hover:bg-gray-50">
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {client.id}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="text-sm font-medium text-gray-900">
                                                                {client.name_of_client}
                                                            </div>
                                                            {client.to_title && (
                                                                <div className="text-sm text-gray-500">
                                                                    {client.to_title}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <div className="text-sm text-gray-900">
                                                                {client.primary_email_1 || client.email}
                                                            </div>
                                                            {client.primary_email_2 && (
                                                                <div className="text-sm text-gray-500">
                                                                    {client.primary_email_2}
                                                                </div>
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {client.company ? client.company.name : '-'}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                {client.contacts ? client.contacts.length : 0} contacts
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
                                                                client.status === 'active' 
                                                                    ? 'bg-green-100 text-green-800' 
                                                                    : 'bg-red-100 text-red-800'
                                                            }`}>
                                                                {client.status}
                                                            </span>
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                            <div className="flex space-x-2">
                                                                <Link
                                                                    href={`/admin/client/${client.id}`}
                                                                    className="text-indigo-600 hover:text-indigo-900"
                                                                >
                                                                    View
                                                                </Link>
                                                                <Link
                                                                    href={`/admin/client/${client.id}/edit`}
                                                                    className="text-green-600 hover:text-green-900"
                                                                >
                                                                    Edit
                                                                </Link>
                                                                {client.contacts && client.contacts.length > 0 && (
                                                                    <Link
                                                                        href={route('client.contacts', client.id)}
                                                                        className="text-blue-600 hover:text-blue-900"
                                                                    >
                                                                        Contacts
                                                                    </Link>
                                                                )}
                                                                {client.taxes && client.taxes.length > 0 && (
                                                                    <Link
                                                                        href={route('client.taxes', client.id)}
                                                                        className="text-purple-600 hover:text-purple-900"
                                                                    >
                                                                        Taxes
                                                                    </Link>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>

                            {/* Pagination */}
                            {clients.data.length > 0 && (
                                <div className="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                                    <div className="flex flex-1 justify-between sm:hidden">
                                        {clients.prev_page_url && (
                                            <Link
                                                href={clients.prev_page_url}
                                                className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Previous
                                            </Link>
                                        )}
                                        {clients.next_page_url && (
                                            <Link
                                                href={clients.next_page_url}
                                                className="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Next
                                            </Link>
                                        )}
                                    </div>
                                    <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p className="text-sm text-gray-700">
                                                Showing{' '}
                                                <span className="font-medium">{clients.from || 0}</span>
                                                {' '}to{' '}
                                                <span className="font-medium">{clients.to || 0}</span>
                                                {' '}of{' '}
                                                <span className="font-medium">{clients.total}</span>
                                                {' '}results
                                            </p>
                                        </div>
                                        <div className="flex space-x-1">
                                            {clients.prev_page_url && (
                                                <Link
                                                    href={clients.prev_page_url}
                                                    className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50"
                                                >
                                                    Previous
                                                </Link>
                                            )}
                                            
                                            {/* Page numbers would go here - simplified for now */}
                                            <span className="relative inline-flex items-center border border-gray-300 bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                                                {clients.current_page}
                                            </span>
                                            
                                            {clients.next_page_url && (
                                                <Link
                                                    href={clients.next_page_url}
                                                    className="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50"
                                                >
                                                    Next
                                                </Link>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}