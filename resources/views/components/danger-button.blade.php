<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-brand-danger hover:bg-red-500 active:bg-red-700 text-white border border-red-500/30 font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-red-500/20 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-brand-card disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
