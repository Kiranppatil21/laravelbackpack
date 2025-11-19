import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Edit({ id }) {
    const [loading, setLoading] = useState(true);
    const form = useForm({ name: '' });

    useEffect(() => {
        window.axios.get(`/api/agencies/${id}`)
            .then((res) => {
                form.setData('name', res.data.name || '');
            })
            .catch(() => {})
            .finally(() => setLoading(false));
    }, [id]);

    const submit = (e) => {
        e.preventDefault();
        form.put(`/api/agencies/${id}`, {
            onSuccess: () => router.visit('/agencies'),
        });
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
                                <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.name && <div className="text-red-600 text-sm">{form.errors.name}</div>}
                            </div>

                            <div className="flex items-center gap-2">
                                <button type="submit" className="btn btn-primary" disabled={form.processing}>Save</button>
                                <Link href="/agencies" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
