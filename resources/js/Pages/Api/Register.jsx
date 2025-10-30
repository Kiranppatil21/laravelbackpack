import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function ApiRegister() {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    const [error, setError] = useState(null);
    const [processing, setProcessing] = useState(false);

    const submit = async (e) => {
        e.preventDefault();
        setError(null);

        if (!name || !email || !password) {
            setError('Please fill all required fields.');
            return;
        }

        if (password !== passwordConfirmation) {
            setError('Passwords do not match.');
            return;
        }

        setProcessing(true);
        try {
            const resp = await fetch('/api/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({ name, email, password, password_confirmation: passwordConfirmation }),
            });

            if (!resp.ok) {
                const data = await resp.json().catch(() => ({}));
                setError(data.message || 'Registration failed');
                setProcessing(false);
                return;
            }

            const data = await resp.json();
            localStorage.setItem('api_token', data.token);
            // After register, the server may have sent a verification email; still go to dashboard but show notice
            window.location = '/api-dashboard';
        } catch (err) {
            setError('Network error');
            setProcessing(false);
        }
    };

    return (
        <div className="max-w-md mx-auto mt-10">
            <Head title="API Register" />

            <h2 className="text-2xl font-semibold mb-4">Register (API)</h2>

            {error && <div className="mb-4 text-red-600">{error}</div>}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <label className="block text-sm font-medium text-gray-700">Name</label>
                    <input value={name} onChange={(e) => setName(e.target.value)} className="mt-1 block w-full rounded border-gray-300" required />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} className="mt-1 block w-full rounded border-gray-300" required />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} className="mt-1 block w-full rounded border-gray-300" required />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <input type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} className="mt-1 block w-full rounded border-gray-300" required />
                </div>

                <div className="flex items-center justify-between">
                    <div>
                        <a href="/api-login" className="text-sm text-indigo-600 underline">Already have an API account?</a>
                    </div>
                    <div>
                        <button type="submit" disabled={processing} className="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded">
                            {processing ? 'Registering…' : 'Register and create tenant'}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );
}
