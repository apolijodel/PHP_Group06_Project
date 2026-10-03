<?php
/**
 * Theme bootstrap. Included inside <head>, BEFORE the stylesheet link, on
 * every page that has a <head> of its own (customer header, admin header,
 * admin login).
 *
 * The <script> runs synchronously, so data-theme is on <html> before the
 * first paint — without it the page would render cream and then flip to dark,
 * which is the classic dark-mode flash.
 *
 * The preference lives in localStorage, not the database: it is a per-device
 * display choice, it must be readable before PHP could send it, and keeping
 * it out of `users` means no schema change and no write on every toggle.
 */
?>
<script>
(function () {
    try {
        /* Signing out hands the browser back to whoever sits down next, so the
           stored choice goes with the session. Cleared here rather than in the
           main script because this runs before the first paint - clearing it
           later would show a dark page that then flips to light, which is the
           flash this file exists to avoid. */
        if (/[?&]signedout=1(?:&|$)/.test(location.search)) {
            localStorage.removeItem('markme-theme');
            return;
        }

        var saved = localStorage.getItem('markme-theme');
        if (saved === 'dark' || saved === 'light') {
            document.documentElement.setAttribute('data-theme', saved);
        }
    } catch (err) {
        /* Private mode or blocked storage: fall through to the light theme. */
    }
})();
</script>
