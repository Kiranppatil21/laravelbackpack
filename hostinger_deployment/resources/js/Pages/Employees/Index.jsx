import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Index() {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        window.axios.get('/api/employees')
            .then((res) => setItems(res.data.data || res.data))
            .catch(() => setItems([]))
            .finally(() => setLoading(false));
    }, []);

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold">Employees</h2>}>
            <Head title="Employees" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="flex justify-end mb-4">
                        <Link href="/employees/create" className="btn btn-primary">New Employee</Link>
                    </div>

                    <div className="bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {loading ? (
                                <div>Loading…</div>
                            ) : (
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr>
                                            <th className="px-6 py-2 text-left">Name</th>
                                            <th className="px-6 py-2">Email</th>
                                            <th className="px-6 py-2">Client</th>
                                            <th className="px-6 py-2">KYC</th>
                                            <th className="px-6 py-2">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {items.map((e) => (
                                            <tr key={e.id}>
                                                <td className="px-6 py-2">{e.name || `${e.first_name || ''} ${e.last_name || ''}`}</td>
                                                <td className="px-6 py-2">{e.email}</td>
                                                <td className="px-6 py-2">{(e.client && e.client.name) || '—'}</td>
                                                <td className="px-6 py-2">{e.kyc_status}</td>
                                                <td className="px-6 py-2">
                                                    <Link href={`/employees/${e.id}/edit`} className="text-blue-600">Edit</Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
