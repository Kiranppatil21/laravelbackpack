import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Create() {
    const [clients, setClients] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [searchLoading, setSearchLoading] = useState(false);
    const minSearchChars = 2;
    const form = useForm({ first_name: '', last_name: '', email: '', phone: '', client_id: '', aadhar: null, pan: null, police_verification: null });
    const [highlightIndex, setHighlightIndex] = useState(-1);

    useEffect(() => {
        // load initial small set
        window.axios.get('/api/clients')
            .then((res) => setClients(res.data.data || res.data))
            .catch(() => setClients([]));
    }, []);

    // debounced search -> populate searchResults
    useEffect(() => {
        if (!searchQuery || searchQuery.length < minSearchChars) {
            setSearchResults([]);
            setSearchLoading(false);
            return;
        }

        setSearchLoading(true);

        const t = setTimeout(() => {
            window.axios.get('/api/clients', { params: { q: searchQuery } })
                .then((res) => setSearchResults(res.data.data || res.data))
                .catch(() => setSearchResults([]))
                .finally(() => setSearchLoading(false));
        }, 300);

        return () => clearTimeout(t);
    }, [searchQuery]);

    const onSearchKeyDown = (e) => {
        if (!searchResults.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setHighlightIndex((i) => (i >= searchResults.length - 1 ? 0 : i + 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setHighlightIndex((i) => (i <= 0 ? searchResults.length - 1 : i - 1));
        } else if (e.key === 'Enter') {
            if (highlightIndex >= 0 && highlightIndex < searchResults.length) {
                const c = searchResults[highlightIndex];
                form.setData('client_id', c.id);
                setSearchQuery(c.name);
                setSearchResults([]);
                setHighlightIndex(-1);
            }
        } else if (e.key === 'Escape') {
            setSearchResults([]);
            setHighlightIndex(-1);
        }
    };

    const submit = (e) => {
        e.preventDefault();

        form.post('/api/employees', {
            onSuccess: () => router.visit('/employees'),
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-xl">New Employee</h2>}>
            <Head title="New Employee" />
            <div className="py-6">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} encType="multipart/form-data">
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">First name</label>
                                <input data-cy="employee-first-name" name="first_name" value={form.data.first_name} onChange={(e) => form.setData('first_name', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.first_name && <div className="text-red-600 text-sm">{form.errors.first_name}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Last name</label>
                                <input data-cy="employee-last-name" name="last_name" value={form.data.last_name} onChange={(e) => form.setData('last_name', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.last_name && <div className="text-red-600 text-sm">{form.errors.last_name}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Email</label>
                                <input data-cy="employee-email" name="email" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} className="mt-1 block w-full" />
                                {form.errors.email && <div className="text-red-600 text-sm">{form.errors.email}</div>}
                            </div>

                            <div className="mb-4 relative">
                                <label className="block text-sm font-medium text-gray-700">Client (optional)</label>
                                <input
                                    id="employee-client-search"
                                    data-cy="employee-client-search"
                                    aria-autocomplete="list"
                                    aria-controls="employee-client-listbox"
                                    aria-expanded={searchResults.length > 0}
                                    aria-activedescendant={highlightIndex >= 0 && searchResults[highlightIndex] ? `employee-client-option-${searchResults[highlightIndex].id}` : undefined}
                                    placeholder="Search clients..."
                                    value={searchQuery}
                                    onChange={(e) => setSearchQuery(e.target.value)}
                                    onKeyDown={onSearchKeyDown}
                                    className="mt-1 block w-full mb-2"
                                />

                                {searchLoading && <div className="text-sm text-gray-500 mb-2">Searching…</div>}
                                {!searchLoading && searchQuery.length >= minSearchChars && searchResults.length === 0 && (
                                    <div className="text-sm text-gray-500 mb-2">No clients found</div>
                                )}

                                {/* Screen reader announcements for search status / results */}
                                <div className="sr-only" aria-live="polite">
                                    {searchLoading
                                        ? `Searching for "${searchQuery}"`
                                        : (searchQuery.length >= minSearchChars
                                            ? (searchResults.length > 0 ? `${searchResults.length} results available` : 'No clients found')
                                            : '')}
                                </div>

                                {/* Typeahead list */}
                                {searchResults.length > 0 && (
                                    <ul id="employee-client-listbox" className="border rounded bg-white max-h-48 overflow-auto mb-2" role="listbox" aria-labelledby="employee-client-search">
                                        {searchResults.map((c, idx) => (
                                            <li
                                                id={`employee-client-option-${c.id}`}
                                                key={c.id}
                                                role="option"
                                                aria-selected={highlightIndex === idx}
                                                className={`px-3 py-2 cursor-pointer ${highlightIndex === idx ? 'bg-gray-200' : 'hover:bg-gray-100'}`}
                                                onMouseEnter={() => setHighlightIndex(idx)}
                                                onMouseLeave={() => setHighlightIndex(-1)}
                                                onClick={() => { form.setData('client_id', c.id); setSearchQuery(c.name); setSearchResults([]); setHighlightIndex(-1); }}
                                            >
                                                {c.name}
                                            </li>
                                        ))}
                                    </ul>
                                )}

                                <select data-cy="employee-client-select" value={form.data.client_id} onChange={(e) => form.setData('client_id', e.target.value)} className="mt-1 block w-full">
                                    <option value="">— none —</option>
                                    {clients.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                {form.errors.client_id && <div className="text-red-600 text-sm">{form.errors.client_id}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Aadhar (PDF/JPG/PNG)</label>
                                <input data-cy="employee-aadhar" type="file" onChange={(e) => form.setData('aadhar', e.target.files[0])} className="mt-1 block w-full" />
                                {form.errors.aadhar && <div className="text-red-600 text-sm">{form.errors.aadhar}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">PAN (PDF/JPG/PNG)</label>
                                <input data-cy="employee-pan" type="file" onChange={(e) => form.setData('pan', e.target.files[0])} className="mt-1 block w-full" />
                                {form.errors.pan && <div className="text-red-600 text-sm">{form.errors.pan}</div>}
                            </div>

                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Police verification (PDF/JPG/PNG)</label>
                                <input data-cy="employee-police" type="file" onChange={(e) => form.setData('police_verification', e.target.files[0])} className="mt-1 block w-full" />
                                {form.errors.police_verification && <div className="text-red-600 text-sm">{form.errors.police_verification}</div>}
                            </div>

                            <div className="flex items-center gap-2">
                                <button data-cy="employee-create-submit" type="submit" className="btn btn-primary" disabled={form.processing}>Create</button>
                                <Link href="/employees" className="btn">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
