import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Edit({ id }) {
    const [name, setName] = useState('');
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        window.axios.get(`/api/agencies/${id}`)
            .then((res) => {
                setName(res.data.name || '');
            })
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [id]);

    const submit = (e) => {
        e.preventDefault();
        setErrors({});
        if (! name.trim()) {
            setErrors({ name: 'Name is required' });
            return;
        }

        setSubmitting(true);
        window.axios.put(`/api/agencies/${id}`, { name })
            .then(() => window.location.href = '/agencies')
            .catch((err) => {
                if (err.response && err.response.status === 422) {
                    setErrors(err.response.data.errors || {});
                }
            })
            .finally(() => setSubmitting(false));
    };

    if (loading) {
        return <AuthenticatedLayout header={<h2 className="text-xl">Edit Agency</h2>}><div className="p-6">Loading…</div></AuthenticatedLayout>;
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl">Edit Agency</h2>}>
            <Head title="Edit Agency" />
            <div className="py-6">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit}>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Name</label>
                                <input value={name} onChange={(e) => setName(e.target.value)} className="mt-1 block w-full" />
                                {errors.name && <div className="text-red-600 text-sm">{errors.name}</div>}
                            </div>

                            <div className="flex items-center gap-2">
                                <button type="submit" className="btn btn-primary" disabled={submitting}>Save</button>
                                <Link href="/agencies" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
