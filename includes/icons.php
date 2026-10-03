<?php
/**
 * One icon set for the whole site — a single stroke style (1.75, round caps)
 * so nothing ever looks like it came from a different library.
 *
 * Usage:  <?= icon('cart') ?>          <?= icon('check', 20) ?>
 *
 * Icons are decorative by default (aria-hidden). Pass a $label when the icon
 * is the only content of a control and nothing else names it.
 */

function icon(string $name, int $size = 20, string $class = '', ?string $label = null): string
{
    static $paths = [
        // Navigation & chrome
        'search'      => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.2-3.2"/>',
        'cart'        => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'user'        => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'menu'        => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'close'       => '<path d="M18 6L6 18M6 6l12 12"/>',
        'chevron-right' => '<path d="M9 18l6-6-6-6"/>',
        'chevron-left'  => '<path d="M15 18l-6-6 6-6"/>',
        'chevron-down'  => '<path d="M6 9l6 6 6-6"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-left'  => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        'external'    => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/>',

        // Status & feedback
        'check'       => '<path d="M20 6L9 17l-5-5"/>',
        'check-circle'=> '<path d="M22 11.1V12a10 10 0 1 1-5.9-9.1"/><path d="M9 11l3 3L22 4"/>',
        'info'        => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'alert'       => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'star'        => '<path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3-6.2 3.3L7 14.2 2 9.3l6.9-1L12 2z"/>',

        // Customization studio
        'shapes'      => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><circle cx="17.5" cy="7" r="4"/><path d="M7 14l4.5 7h-9L7 14z"/>',
        'palette'     => '<path d="M12 21a9 9 0 1 1 9-9c0 1.7-1.3 3-3 3h-1.5a2.5 2.5 0 0 0-1.8 4.2A1.9 1.9 0 0 1 12 21z"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="10.5" r="1"/>',
        'image'       => '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'upload'      => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v13"/>',
        'type'        => '<path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/>',
        'droplet'     => '<path d="M12 2.7l5.4 5.7a7.6 7.6 0 1 1-10.8 0L12 2.7z"/>',
        'eye'         => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
        'sparkle'     => '<path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3z"/><path d="M19 16l.8 2.2L22 19l-2.2.8L19 22l-.8-2.2L16 19l2.2-.8L19 16z"/>',
        'wand'        => '<path d="M15 4V2M15 16v-2M8 9h2M20 9h2M17.8 11.8l1.4 1.4M17.8 6.2l1.4-1.4M3 21l9-9"/>',

        // Commerce
        'package'     => '<path d="M16.5 9.4L7.5 4.2"/><path d="M21 16V8a2 2 0 0 0-1-1.7l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.7l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="M3.3 7L12 12l8.7-5M12 22V12"/>',
        'truck'       => '<rect x="1" y="6" width="14" height="10" rx="1.5"/><path d="M15 9h4l3 3.5V16h-7z"/><circle cx="6" cy="18.5" r="2"/><circle cx="18" cy="18.5" r="2"/>',
        'tag'         => '<path d="M20.6 13.2l-7.4 7.4a2 2 0 0 1-2.8 0l-7-7A2 2 0 0 1 3 12.2V5a2 2 0 0 1 2-2h7.2a2 2 0 0 1 1.4.6l7 7a2 2 0 0 1 0 2.6z"/><path d="M7.5 7.5h.01"/>',
        'wallet'      => '<path d="M20 7H5a2 2 0 0 1 0-4h13v4"/><path d="M3 5v14a2 2 0 0 0 2 2h15V7H5"/><path d="M16 13h2"/>',
        'card'        => '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/><path d="M6 15h4"/>',
        'bank'        => '<path d="M3 10l9-6 9 6"/><path d="M5 10v9M9.7 10v9M14.3 10v9M19 10v9"/><path d="M3 21h18"/>',
        'receipt'     => '<path d="M5 2v20l2.5-1.6L10 22l2-1.6L14 22l2.5-1.6L19 22V2H5z"/><path d="M9 8h6M9 12h6M9 16h3"/>',

        // Account & admin
        'dashboard'   => '<rect x="3" y="3" width="7.5" height="9" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="5" rx="1.5"/><rect x="13.5" y="12" width="7.5" height="9" rx="1.5"/><rect x="3" y="16" width="7.5" height="5" rx="1.5"/>',
        'users'       => '<path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16.5 3.1a4 4 0 0 1 0 7.8"/>',
        'list'        => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3.5 6h.01M3.5 12h.01M3.5 18h.01"/>',
        'grid'        => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'chart'       => '<path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/>',
        'settings'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 9 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 4.6 9a1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/>',
        'logout'      => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
        'pin'         => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'mail'        => '<rect x="2" y="4" width="20" height="16" rx="2.5"/><path d="M2.5 6.5L12 13l9.5-6.5"/>',
        'phone'       => '<path d="M21.5 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 1.6 4.2 2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.7c.1 1 .3 1.9.7 2.8a2 2 0 0 1-.5 2.1L7.9 9.8a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'clock'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'heart'       => '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 1 0-7.1 7.1l1.7 1.7L12 21.2l7.1-7.1 1.7-1.7a5 5 0 0 0 0-7.1z"/>',
        'shield'      => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/>',
        'leaf'        => '<path d="M11 20A7 7 0 0 1 4 13c0-6 6-10 16-10 0 10-4 16-10 16-1.7 0-3-.6-4-1.5"/><path d="M4 21c2-6 6-9 11-11"/>',
        'scissors'    => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4L8.1 15.9M14.5 14.5L20 20M8.1 8.1L10.5 10.5"/>',
        'book'        => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'bookmark'    => '<path d="M19 21l-7-4.5L5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',

        // Appearance
        'sun'         => '<circle cx="12" cy="12" r="4.2"/><path d="M12 1.5v2.2M12 20.3v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M1.5 12h2.2M20.3 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"/>',
        'moon'        => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/>',

        // Editing
        'plus'        => '<path d="M12 5v14M5 12h14"/>',
        'minus'       => '<path d="M5 12h14"/>',
        'edit'        => '<path d="M11 4H5a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-6"/><path d="M18.4 2.6a2 2 0 0 1 2.8 2.8L12 14.6l-3.8.9.9-3.8 9.3-9.1z"/>',
        'trash'       => '<path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/>',
        'refresh'     => '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 4v5h-5"/>',
        'filter'      => '<path d="M22 3H2l8 9.5V20l4 2v-9.5L22 3z"/>',
        'save'        => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/>',
        'key'         => '<circle cx="7.5" cy="15.5" r="4.5"/><path d="M10.7 12.3L21 2"/><path d="M17 6l3 3"/><path d="M14 9l3 3"/>',
        'store'       => '<path d="M3 9l1.5-5h15L21 9"/><path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/><path d="M4.5 11.5V20a1 1 0 0 0 1 1h13a1 1 0 0 0 1-1v-8.5"/><path d="M9.5 21v-6h5v6"/>',
        'layers'      => '<path d="M12 2.7L2.5 7.5 12 12.3l9.5-4.8L12 2.7z"/><path d="M2.5 16.5L12 21.3l9.5-4.8"/><path d="M2.5 12L12 16.8l9.5-4.8"/>',
        'camera'      => '<path d="M21 19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h3l1.6-2.4a1 1 0 0 1 .8-.6h3.2a1 1 0 0 1 .8.6L16 7h3a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="3.5"/>',
        'sort'        => '<path d="M7 4v16M7 20l-3.5-3.5M7 20l3.5-3.5"/><path d="M17 20V4M17 4l-3.5 3.5M17 4l3.5 3.5"/>',
    ];

    $d = $paths[$name] ?? $paths['info'];
    $cls = trim('mm-icon ' . $class);
    $a11y = $label !== null
        ? ' role="img" aria-label="' . e($label) . '"'
        : ' aria-hidden="true" focusable="false"';

    return '<svg class="' . e($cls) . '" width="' . $size . '" height="' . $size . '"'
        . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"'
        . ' stroke-linecap="round" stroke-linejoin="round"' . $a11y . '>' . $d . '</svg>';
}

/** Filled star (rating display) — the one exception to the stroke-only set. */
function icon_star_filled(int $size = 15): string
{
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor"'
        . ' aria-hidden="true" focusable="false"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8-6.2-3.3-6.2 3.3L7 14.2 2 9.3l6.9-1L12 2z"/></svg>';
}
