@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-brand-surface border-brand-border text-slate-100 placeholder-slate-500 focus:border-brand-blue focus:ring-1 focus:ring-brand-blue rounded-xl shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed transition-colors']) }}>
