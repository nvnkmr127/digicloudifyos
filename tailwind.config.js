import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#4f46e5', // indigo-600
                    hover: '#4338ca',   // indigo-700
                    soft: '#eef2ff',   // indigo-50
                    dark: '#3730a3',    // indigo-800
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                },
                secondary: {
                    DEFAULT: '#f59e0b', // amber-500
                    hover: '#d97706',   // amber-600
                    soft: '#fffbeb',   // amber-50
                },
                success: {
                    DEFAULT: '#10b981', // emerald-500
                    hover: '#059669',   // emerald-600
                    soft: '#ecfdf5',   // emerald-50
                },
                danger: {
                    DEFAULT: '#ef4444', // red-500
                    hover: '#dc2626',   // red-600
                    soft: '#fef2f2',   // red-50
                },
                warning: {
                    DEFAULT: '#f59e0b', // amber-500
                    hover: '#d97706',   // amber-600
                    soft: '#fffbeb',   // amber-50
                },
                info: {
                    DEFAULT: '#3b82f6', // blue-500
                    hover: '#2563eb',   // blue-600
                    soft: '#eff6ff',   // blue-50
                },
                facebook: {
                    DEFAULT: '#1877F2',
                    hover: '#166fe5',
                },
                brand: {
                    black: '#111827', // gray-900
                    muted: '#9ca3af', // gray-400
                    light: '#f9fafb', // gray-50
                },
                text: {
                    primary: '#111827', // gray-900
                    muted: '#6b7280',   // gray-500
                    secondary: '#4b5563', // gray-600
                }
            },
            borderRadius: {
                'card': '1.5rem',
                'card-premium': '2.5rem',
                'card-xl': '3rem',
                'button': '1rem',
                'input': '0.75rem',
                'element': '0.75rem',
            },
            letterSpacing: {
                'branding': '0.2em',
                'branding-wide': '0.3em',
            },
            fontSize: {
                'tiny': ['11px', '0.75rem'],
            },
            boxShadow: {
                'premium': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
                'brand-glow': '0 20px 40px -15px rgba(79, 70, 229, 0.2)',
            }
        },
    },

    plugins: [forms],
};
