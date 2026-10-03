<?php
/**
 * The Appearance panel, shared by the administrator and staff settings
 * screens.
 *
 * It sits here rather than in either page because the theme is a personal,
 * per-device choice that belongs to whoever is signed in — it is the one part
 * of Settings that is not about configuring the shop, so it is the one part
 * staff get. Keeping a single copy means the two panels cannot drift.
 *
 * The buttons carry no state of their own: script.js reads the stored
 * preference and sets aria-pressed, so this is the same control wherever it
 * appears.
 */
?>
<section class="card card-flush">
    <div class="panel-head">
        <h2 class="d-flex align-items-center gap-2"><?= icon('sun', 18) ?> Appearance</h2>
    </div>
    <div class="panel-body">
        <div class="setting-row">
            <div>
                <p class="setting-title">Theme</p>
                <p class="form-text" style="margin:0">Applies to
                    <?= is_admin() ? 'the admin panel' : 'the staff panel' ?> and the storefront on
                    this device. It is cleared when you sign out, so a shared computer goes back to
                    the light theme for whoever uses it next.</p>
            </div>
            <div class="theme-toggle" role="group" aria-label="Colour theme">
                <button type="button" data-theme-option="light" aria-pressed="true">
                    <?= icon('sun', 16) ?> Light
                </button>
                <button type="button" data-theme-option="dark" aria-pressed="false">
                    <?= icon('moon', 16) ?> Dark
                </button>
            </div>
        </div>
    </div>
</section>
