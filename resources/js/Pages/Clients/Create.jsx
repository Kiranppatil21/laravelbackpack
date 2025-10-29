import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Create() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [agencyId, setAgencyId] = useState('');
    const [agencies, setAgencies] = useState([]);
    const [errors, setErrors] = useState({});
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        window.axios.get('/api/agencies')
            .then((res) => setAgencies(res.data.data || res.data))
            .catch(() => setAgencies([]));
    }, []);

    const submit = (e) => {
        e.preventDefault();
        setErrors({});
        const payload = { name, email, agency_id: agencyId || null };
        if (! name.trim()) return setErrors({ name: 'Name required' });
        if (! email.trim()) return setErrors({ email: 'Email required' });

        setSubmitting(true);
        window.axios.post('/api/clients', payload)
            .then(() => window.location.href = '/clients')
            .catch((err) => {
                if (err.response && err.response.status === 422) setErrors(err.response.data.errors || {});
            })
            .finally(() => setSubmitting(false));
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl">New Client</h2>}>
            <Head title="New Client" />
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
                                <button type="submit" className="btn btn-primary" disabled={submitting}>Create</button>
                                <Link href="/clients" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
