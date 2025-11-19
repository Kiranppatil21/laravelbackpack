import { Head } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function ApiDashboard() {
    const [user, setUser] = useState(null);
    const [message, setMessage] = useState('Loading...');

    useEffect(() => {
        const token = localStorage.getItem('api_token');
        if (!token) {
            setMessage('No token found. Please log in.');
            return;
        }

        (async () => {
            try {
                const resp = await fetch('/api/user', {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: 'application/json',
                    },
                });

                if (!resp.ok) {
                    setMessage('Failed to fetch user.');
                    return;
                }

                const data = await resp.json();
                setUser(data);
                setMessage(null);
            } catch (e) {
                setMessage('Network error');
            }
        })();
    }, []);

    const logout = async () => {
        const token = localStorage.getItem('api_token');
        if (!token) return (window.location = '/api-login');

        await fetch('/api/logout', {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        }).catch(() => {});

        localStorage.removeItem('api_token');
        window.location = '/api-login';
    };

    return (
        <div className="max-w-2xl mx-auto mt-10">
            <Head title="API Dashboard" />
            <div className="flex items-center justify-between">
                <h2 className="text-2xl font-semibold">API Dashboard</h2>
                <button onClick={logout} className="px-3 py-1 bg-gray-200 rounded">Logout</button>
            </div>

            {message && (
                <div className="mt-4 text-gray-600">{message}</div>
            )}

            {user && (
                <div className="mt-4">
                    <div><strong>Name:</strong> {user.name}</div>
                    <div><strong>Email:</strong> {user.email}</div>
                    <div><strong>Roles:</strong> {user.roles?.map(r => r.name).join(', ')}</div>
                </div>
            )}
        </div>
    );
}
