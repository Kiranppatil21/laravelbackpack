import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Edit({ id }) {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [agencyId, setAgencyId] = useState('');
    const [agencies, setAgencies] = useState([]);
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        Promise.all([
            window.axios.get(`/api/clients/${id}`),
            window.axios.get('/api/agencies'),
        ])
        .then(([clientRes, agenciesRes]) => {
            const c = clientRes.data;
            setName(c.name || '');
            setEmail(c.email || '');
            setAgencyId(c.agency ? c.agency.id : '');
            setAgencies(agenciesRes.data.data || agenciesRes.data);
        })
        .catch(() => {})
        .finally(() => setLoading(false));
    }, [id]);

    const submit = (e) => {
        e.preventDefault();
        setErrors({});
        const payload = { name, email, agency_id: agencyId || null };
        if (! name.trim()) return setErrors({ name: 'Name required' });
        if (! email.trim()) return setErrors({ email: 'Email required' });

        setSubmitting(true);
        window.axios.put(`/api/clients/${id}`, payload)
            .then(() => window.location.href = '/clients')
            .catch((err) => {
                if (err.response && err.response.status === 422) setErrors(err.response.data.errors || {});
            })
            .finally(() => setSubmitting(false));
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
                                <input value={name} onChange={(e) => setName(e.target.value)} className="mt-1 block w-full" />
                                {errors.name && <div className="text-red-600 text-sm">{errors.name}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Email</label>
                                <input value={email} onChange={(e) => setEmail(e.target.value)} className="mt-1 block w-full" />
                                {errors.email && <div className="text-red-600 text-sm">{errors.email}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Agency (optional)</label>
                                <select value={agencyId} onChange={(e) => setAgencyId(e.target.value)} className="mt-1 block w-full">
                                    <option value="">— none —</option>
                                    {agencies.map(a => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                                {errors.agency_id && <div className="text-red-600 text-sm">{errors.agency_id}</div>}
                            </div>

                            <div className="flex items-center gap-2">
                                <button type="submit" className="btn btn-primary" disabled={submitting}>Save</button>
                                <Link href="/clients" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
