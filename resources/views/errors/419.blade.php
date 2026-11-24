<x-error-page code="419" title="This page expired" icon="clock" tone="amber"
              message="You waited a while, or your session ended, so nothing was saved. Go back, refresh the page, and send the form again.">
    <x-action type="button" icon="back" variant="primary" onclick="history.back()">Go back</x-action>
    <x-action :href="route('login')" icon="lock">Log in</x-action>
</x-error-page>