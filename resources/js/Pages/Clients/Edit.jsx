import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Edit({ id }) {
    const [loading, setLoading] = useState(true);
    const form = useForm({ name: '', email: '', agency_id: '' });
    const [agencies, setAgencies] = useState([]);

    useEffect(() => {
        Promise.all([
            window.axios.get(`/api/clients/${id}`),
            window.axios.get('/api/agencies'),
        ])
        .then(([clientRes, agenciesRes]) => {
            const c = clientRes.data;
            form.setData('name', c.name || '');
            form.setData('email', c.email || '');
            form.setData('agency_id', c.agency ? c.agency.id : '');
            setAgencies(agenciesRes.data.data || agenciesRes.data);
        })
        .catch(() => {})
        .finally(() => setLoading(false));
    }, [id]);

    const submit = (e) => {
        e.preventDefault();
        form.put(`/api/clients/${id}`, {
            onSuccess: () => router.visit('/clients'),
        });
    };

    if (loading) {
        return <AuthenticatedLayout header={<h2 className="text-xl">Edit Client</h2>}><div className="p-6">Loading…</div></AuthenticatedLayout>;
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl">Edit Client</h2>}>
            <Head title="Edit Client" />
            <div className="py-6">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Name</label>
                                <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.name && <div className="text-red-600 text-sm">{form.errors.name}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Email</label>
                                <input value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.email && <div className="text-red-600 text-sm">{form.errors.email}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Agency (optional)</label>
                                <select value={form.data.agency_id} onChange={(e) => form.setData('agency_id', e.target.value)} className="mt-1 block w-full">
                                    <option value="">— none —</option>
                                    {agencies.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                                {form.errors.agency_id && <div className="text-red-600 text-sm">{form.errors.agency_id}</div>}
                            </div>

                            <div className="flex items-center gap-2">
                                <button type="submit" className="btn btn-primary" disabled={form.processing}>Save</button>
                                <Link href="/clients" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
