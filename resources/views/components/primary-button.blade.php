<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-brand-blue hover:bg-brand-blueHover active:bg-blue-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-glow-blue transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-cyan focus:ring-offset-2 focus:ring-offset-brand-card disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
