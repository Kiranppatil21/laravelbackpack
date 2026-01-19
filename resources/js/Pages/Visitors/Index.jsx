import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Index() {
    const [items, setItems] = useState([]);
    const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 25, total: 0 });
    const [loading, setLoading] = useState(true);

    const [filters, setFilters] = useState({ search: '', host_id: '', from: '', to: '', per_page: 25, page: 1 });

    const fetchData = (opts = {}) => {
        setLoading(true);
        const q = new URLSearchParams({
            ...(filters || {}),
            ...opts,
        });

        window.axios.get('/admin/api/visitors/logs?' + q.toString())
            .then((res) => {
                setItems(res.data.data || []);
                setMeta({
                    current_page: res.data.current_page || res.data.meta?.current_page || 1,
                    last_page: res.data.last_page || res.data.meta?.last_page || 1,
                    per_page: res.data.per_page || res.data.meta?.per_page || filters.per_page,
                    total: res.data.total || res.data.meta?.total || 0,
                });
            })
            .catch(() => {
                setItems([]);
                setMeta({ current_page: 1, last_page: 1, per_page: filters.per_page, total: 0 });
            })
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchData({ page: filters.page, per_page: filters.per_page });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [filters.page, filters.per_page]);

    const onSearch = (e) => {
        e.preventDefault();
        setFilters({ ...filters, page: 1 });
        fetchData({ search: filters.search, host_id: filters.host_id, from: filters.from, to: filters.to, page: 1, per_page: filters.per_page });
    };

    const gotoPage = (p) => {
        setFilters({ ...filters, page: p });
    };

    // Server provides ISO timestamps (check_in_at_iso / check_out_at_iso). We'll display locale + relative.
    const relativeTime = (iso) => {
        if (!iso) return '';
        const then = new Date(iso).getTime();
        const now = Date.now();
        const diff = Math.floor((now - then) / 1000);
        const abs = Math.abs(diff);
        if (abs < 60) return `${diff}s ago`;
        if (abs < 3600) return `${Math.floor(diff/60)}m ago`;
        if (abs < 86400) return `${Math.floor(diff/3600)}h ago`;
        return `${Math.floor(diff/86400)}d ago`;
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold">Visitors</h2>}
        >
            <Head title="Visitors" />

            <div className="py-6">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <form onSubmit={onSearch} className="mb-4 flex gap-2 items-end">
                                <div>
                                    <label className="block text-sm">Search</label>
                                    <input value={filters.search} onChange={(e) => setFilters({ ...filters, search: e.target.value })} className="input" />
                                </div>
                                <div>
                                    <label className="block text-sm">Host ID</label>
                                    <input value={filters.host_id} onChange={(e) => setFilters({ ...filters, host_id: e.target.value })} className="input" />
                                </div>
                                <div>
                                    <label className="block text-sm">From</label>
                                    <input type="date" value={filters.from} onChange={(e) => setFilters({ ...filters, from: e.target.value })} className="input" />
                                </div>
                                <div>
                                    <label className="block text-sm">To</label>
                                    <input type="date" value={filters.to} onChange={(e) => setFilters({ ...filters, to: e.target.value })} className="input" />
                                </div>
                                <div>
                                    <label className="block text-sm">Per page</label>
                                    <select value={filters.per_page} onChange={(e) => setFilters({ ...filters, per_page: Number(e.target.value), page: 1 })} className="input">
                                        <option value={10}>10</option>
                                        <option value={25}>25</option>
                                        <option value={50}>50</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" className="btn btn-primary">Apply</button>
                                </div>
                            </form>

                            {loading ? (
                                <div>Loading…</div>
                            ) : (
                                <>
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr>
                                                <th className="px-6 py-2 text-left">Visitor</th>
                                                <th className="px-6 py-2">Host</th>
                                                <th className="px-6 py-2">Check in</th>
                                                <th className="px-6 py-2">Check out</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {items.map((v) => (
                                                <tr key={v.id} data-cy={`visit-row-${v.id}`}>
                                                    <td className="px-6 py-2">{v.visitor ? `${v.visitor.name} (${v.visitor.email || v.visitor.phone || ''})` : '—'}</td>
                                                    <td className="px-6 py-2">{v.host_name || v.host_id || '—'}</td>
                                                    <td className="px-6 py-2">{v.check_in_at_iso ? (new Date(v.check_in_at_iso).toLocaleString() + ' (' + relativeTime(v.check_in_at_iso) + ')') : (v.check_in_at || '—')}</td>
                                                    <td className="px-6 py-2">{v.check_out_at_iso ? (new Date(v.check_out_at_iso).toLocaleString() + ' (' + relativeTime(v.check_out_at_iso) + ')') : (v.check_out_at || '—')}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>

                                    <div className="mt-4 flex items-center justify-between">
                                        <div>Showing {items.length} of {meta.total}</div>
                                        <div className="flex gap-2 items-center">
                                            <button className="btn" disabled={meta.current_page <= 1} onClick={() => gotoPage(meta.current_page - 1)} data-cy="visitors-prev">Prev</button>
                                            <span>Page {meta.current_page} / {meta.last_page}</span>
                                            <button className="btn" disabled={meta.current_page >= meta.last_page} onClick={() => gotoPage(meta.current_page + 1)} data-cy="visitors-next">Next</button>
                                        </div>
                                    </div>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
