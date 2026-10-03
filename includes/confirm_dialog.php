<?php
/**
 * The one confirmation dialog, shared by every destructive action.
 *
 * Included once per page. Any form that carries data-confirm is intercepted
 * by script.js, which fills this dialog from the form's own attributes and
 * only submits once the person confirms:
 *
 *   <form method="post" action="..." data-confirm
 *         data-confirm-title="Delete product?"
 *         data-confirm-body="Delete &quot;Photo Memory Bookmark&quot;?"
 *         data-confirm-note="This action cannot be undone."
 *         data-confirm-action="Delete"
 *         data-confirm-tone="danger">
 *
 * Nothing is deleted until the confirm button is pressed - the dialog does
 * not submit anything by itself, it hands the original form back to the
 * browser. With JavaScript unavailable the form simply submits as it always
 * did, so the action still works; it is the confirmation that is progressive,
 * not the deletion.
 */
?>
<div class="confirm-backdrop" data-confirm-backdrop hidden></div>

<div class="confirm-dialog" data-confirm-dialog role="dialog" aria-modal="true"
     aria-labelledby="confirmTitle" aria-describedby="confirmBody" hidden>
    <h2 class="confirm-title" id="confirmTitle" data-confirm-title>Are you sure?</h2>
    <p class="confirm-body" id="confirmBody" data-confirm-body></p>
    <p class="confirm-note" data-confirm-note>This action cannot be undone.</p>

    <div class="confirm-actions">
        <!-- Cancel comes first and takes focus when the dialog opens, so the
             safe choice is the one a stray Enter or Space lands on. -->
        <button type="button" class="btn btn-secondary" data-confirm-cancel>Cancel</button>
        <button type="button" class="btn btn-danger" data-confirm-ok>Delete</button>
    </div>
</div>
