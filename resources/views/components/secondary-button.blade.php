<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-brand-surface hover:bg-slate-700 active:bg-slate-800 text-slate-200 border border-brand-border font-bold text-xs uppercase tracking-wider rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-cyan focus:ring-offset-2 focus:ring-offset-brand-card disabled:opacity-50 disabled:cursor-not-allowed']) }}>
    {{ $slot }}
</button>
