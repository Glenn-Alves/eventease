<nav aria-label="Help and legal" {{ $attributes->merge(['class' => 'flex flex-wrap gap-x-5 gap-y-1 text-sm']) }}>
    <a href="{{ route('faq') }}" class="tap inline-flex items-center text-gray-700 underline hover:text-slate-900">FAQ</a>
    <a href="{{ route('terms') }}" class="tap inline-flex items-center text-gray-700 underline hover:text-slate-900">Terms and Conditions</a>
    <a href="{{ route('privacy') }}" class="tap inline-flex items-center text-gray-700 underline hover:text-slate-900">Privacy Policy</a>
</nav>