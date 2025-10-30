import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function ApiLogin() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState(null);
    const [processing, setProcessing] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!email || !password) {
            setError('Please provide both email and password.');
            return;
        }

        setProcessing(true);
        try {
            const resp = await fetch('/api/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ email, password }),
            });

            if (!resp.ok) {
                const data = await resp.json().catch(() => ({}));
                setError(data.message || 'Login failed');
                setProcessing(false);
                return;
            }

            const data = await resp.json();
            localStorage.setItem('api_token', data.token);
            // go to API dashboard page (web route)
            window.location = '/api-dashboard';
        } catch (err) {
            setError('Network error');
            setProcessing(false);
        }
    };

    return (
        <div className="max-w-md mx-auto mt-10">
            <Head title="API Login" />
            <h2 className="text-2xl font-semibold mb-4">API Token Login</h2>

            {error && (
                <div className="mb-4 text-red-600">{error}</div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        className="mt-1 block w-full rounded border-gray-300"
                        required
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Password</label>
                    <input
                        type="password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        className="mt-1 block w-full rounded border-gray-300"
                        required
                    />
                </div>

                <div className="flex items-center justify-between">
                    <div>
                        <a href="/api-register" className="text-sm text-indigo-600 underline">Create an API account</a>
                    </div>
                    <div>
                        <button
                            type="submit"
                            className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded"
                            disabled={processing}
                        >
                            {processing ? 'Logging in…' : 'Log in and get token'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
}
