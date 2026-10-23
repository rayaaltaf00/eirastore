import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    darkest: '#090D16',   // Background dasar pekat (deep midnight)
                    card: '#0F172A',      // Permukaan kartu (slate 900)
                    cardHover: '#162033', // Permukaan kartu saat di-hover
                    surface: '#1E293B',   // Elemen input, dropdown, panel (slate 800)
                    border: '#243247',    // Border halus yang tegas namun elegan
                    borderLight: 'rgba(255, 255, 255, 0.08)',
                    blue: '#2563EB',      // Warna aksen utama (Royal Blue solid)
                    blueHover: '#1D4ED8', // Hover tombol utama
                    cyan: '#0EA5E9',      // Aksen kedua (Sky/Cyan untuk badge & highlight)
                    gold: '#F59E0B',      // Aksen bintang rating & status penting
                    success: '#10B981',   // Status selesai / pembayaran sukses
                    danger: '#EF4444',    // Status gagal / batal
                },
            },
            boxShadow: {
                'glow-blue': '0 0 20px -3px rgba(37, 99, 235, 0.25)',
                'glow-cyan': '0 0 20px -3px rgba(14, 165, 233, 0.25)',
                'card-sm': '0 2px 8px 0 rgba(0, 0, 0, 0.4)',
                'card-md': '0 4px 16px 0 rgba(0, 0, 0, 0.5)',
            },
        },
    },

    plugins: [forms],
};
