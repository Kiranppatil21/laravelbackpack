import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';

export default function Create() {
    const form = useForm({ name: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post('/api/agencies', {
            onSuccess: () => router.visit('/agencies'),
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl">New Agency</h2>}>
            <Head title="New Agency" />
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
                                <button type="submit" className="btn btn-primary" disabled={form.processing}>Create</button>
                                <Link href="/agencies" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
