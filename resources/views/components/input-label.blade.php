@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs uppercase tracking-wider text-slate-300']) }}>
    {{ $value ?? $slot }}
</label>
