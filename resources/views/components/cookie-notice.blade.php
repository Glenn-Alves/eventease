@once
    <style>
        .cookie-notice { position: fixed; inset: auto 0 0 0; z-index: 60; padding: 12px; pointer-events: none; }
        .cookie-notice__box { pointer-events: auto; display: flex; flex-wrap: wrap; align-items: center; gap: 12px 20px; max-width: 52rem; margin: 0 auto; padding: 16px 20px; color: #fff; background: #0f172a; border-radius: 12px; box-shadow: 0 10px 30px rgba(0, 0, 0, .35); }
        .cookie-notice__text { flex: 1 1 18rem; margin: 0; font-size: .9rem; line-height: 1.5; }
        .cookie-notice__text a { color: #fff; font-weight: 600; text-decoration: underline; }
        .cookie-notice__btn { min-height: 44px; padding: 0 22px; font-size: .9rem; font-weight: 600; color: #0f172a; background: #fff; border: 0; border-radius: 8px; cursor: pointer; }
        .cookie-notice__btn:hover { background: #e5e7eb; }
        .cookie-notice a:focus-visible, .cookie-notice__btn:focus-visible { outline: 3px solid #a5b4fc; outline-offset: 2px; }

        @media (prefers-reduced-motion: no-preference) {
            .cookie-notice__box { animation: cookie-up .35s ease-out both; }
            @keyframes cookie-up { from { transform: translateY(24px); opacity: 0; } to { transform: none; opacity: 1; } }
        }

        @media print { .cookie-notice { display: none; } }
    </style>
@endonce

<div x-data="{ open: false, key: 'eventease-cookie-notice' }"
     x-init="try { open = localStorage.getItem(key) !== 'v1' } catch (e) { open = true }"
     x-show="open" x-transition.opacity.duration.250ms style="display: none;"
     class="cookie-notice" role="region" aria-label="Cookie notice">

    <div class="cookie-notice__box">
        <p class="cookie-notice__text">
            EventEase uses only the cookies it needs to work, such as keeping you logged in and keeping forms safe.
            We do not use advertising or tracking cookies.
            <a href="{{ route('privacy') }}#cookies">Learn more</a>
        </p>

        <button type="button" class="cookie-notice__btn"
                x-on:click="try { localStorage.setItem(key, 'v1') } catch (e) {} open = false">Got it</button>
    </div>
</div>