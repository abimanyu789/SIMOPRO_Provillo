import React, { useEffect, useState } from 'react';

type ToastProps = {
    message: string;
    type: 'success' | 'error' | 'warning';
    onClose: () => void;
};

export const Toast: React.FC<ToastProps> = ({ message, type, onClose }) => {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const timer = setTimeout(() => {
            setVisible(false);
            setTimeout(onClose, 300); // Allow fade out animation
        }, 3000);

        return () => clearTimeout(timer);
    }, [onClose]);

    const colors = {
        success: 'border-l-4 border-emerald-500 bg-white text-emerald-800 shadow-lg',
        error: 'border-l-4 border-rose-500 bg-white text-rose-800 shadow-lg',
        warning: 'border-l-4 border-amber-500 bg-white text-amber-800 shadow-lg',
    };

    const icons = {
        success: 'ri-checkbox-circle-line text-emerald-500 text-lg',
        error: 'ri-error-warning-line text-rose-500 text-lg',
        warning: 'ri-alert-line text-amber-500 text-lg',
    };

    return (
        <div
            className={`fixed top-5 right-5 z-[9999] flex items-center gap-3 px-5 py-4 rounded-xl min-w-[320px] transition-all duration-300 transform ${
                visible ? 'translate-y-0 opacity-100' : '-translate-y-2 opacity-0'
            } ${colors[type]}`}
        >
            <i className={icons[type]} />
            <div className="flex-1 text-sm font-medium">{message}</div>
            <button onClick={() => setVisible(false)} className="text-gray-400 hover:text-gray-600">
                <i class="ri-close-line" />
            </button>
        </div>
    );
};
