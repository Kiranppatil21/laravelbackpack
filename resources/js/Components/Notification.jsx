import React, { useEffect, useState } from 'react';

export default function Notification({ message, type = 'success', show = false, onClose }) {
    const [isVisible, setIsVisible] = useState(show);

    useEffect(() => {
        setIsVisible(show);
        if (show) {
            const timer = setTimeout(() => {
                handleClose();
            }, 5000); // Auto-close after 5 seconds
            return () => clearTimeout(timer);
        }
    }, [show]);

    const handleClose = () => {
        setIsVisible(false);
        if (onClose) {
            onClose();
        }
    };

    if (!isVisible) return null;

    const typeStyles = {
        success: 'bg-green-100 border-green-500 text-green-700',
        error: 'bg-red-100 border-red-500 text-red-700',
        warning: 'bg-yellow-100 border-yellow-500 text-yellow-700',
        info: 'bg-blue-100 border-blue-500 text-blue-700'
    };

    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    return (
        <div className={`fixed top-4 right-4 z-50 p-4 border-l-4 rounded shadow-lg max-w-sm ${typeStyles[type] || typeStyles.info}`}>
            <div className="flex items-center justify-between">
                <div className="flex items-center">
                    <span className="text-lg mr-2">{icons[type] || icons.info}</span>
                    <span className="text-sm font-medium">{message}</span>
                </div>
                <button
                    onClick={handleClose}
                    className="ml-4 text-gray-400 hover:text-gray-600 focus:outline-none"
                >
                    ×
                </button>
            </div>
        </div>
    );
}