/* MarkMe — UI behaviour.
   Progressive enhancement only: every form still submits without this file. */

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/* ---------------------------------------------------------------- header */

const navToggle = $("#navToggle");
const primaryNav = $("#primaryNav .site-nav");

if (navToggle && primaryNav) {
  navToggle.addEventListener("click", () => {
    const open = primaryNav.classList.toggle("open");
    navToggle.setAttribute("aria-expanded", String(open));
    navToggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
  });
}

/* The global search control was removed from the navbar; the Shop page keeps
   its own search field. Escape still closes the mobile nav drawer. */
document.addEventListener("keydown", (e) => {
  if (e.key !== "Escape") return;
  if (primaryNav?.classList.contains("open")) {
    primaryNav.classList.remove("open");
    navToggle?.setAttribute("aria-expanded", "false");
  }
});

/* --------------------------------------------------- admin sidebar drawer */

const adminBurger = $("#adminBurger");
const adminSide = $("#adminSide");
const adminScrim = $("#adminScrim");

if (adminBurger && adminSide && adminScrim) {
  const setDrawer = (open) => {
    adminSide.classList.toggle("open", open);
    adminScrim.classList.toggle("open", open);
    adminBurger.setAttribute("aria-expanded", String(open));
  };
  adminBurger.addEventListener("click", () => setDrawer(!adminSide.classList.contains("open")));
  adminScrim.addEventListener("click", () => setDrawer(false));
  document.addEventListener("keydown", (e) => e.key === "Escape" && setDrawer(false));
}

/* -------------------------------------------------------- quantity steppers
   Wraps a number input:  [-] [ 3 ] [+]  — clamped to the available stock. */

$$(".qty-stepper").forEach((stepper) => {
  const input = $("input", stepper);
  if (!input) return;

  const min = Number(input.min || 1);
  const max = Number(input.max || input.dataset.maxStock || 99);
  const dec = $("[data-step='-1']", stepper);
  const inc = $("[data-step='1']", stepper);

  const sync = () => {
    let val = parseInt(input.value, 10);
    if (isNaN(val)) val = min;
    val = Math.min(Math.max(val, min), max);
    input.value = String(val);
    if (dec) dec.disabled = val <= min;
    if (inc) inc.disabled = val >= max;
    input.dispatchEvent(new Event("mm:qty", { bubbles: true }));
  };

  [dec, inc].forEach((btn) =>
    btn?.addEventListener("click", () => {
      input.value = String((parseInt(input.value, 10) || min) + Number(btn.dataset.step));
      sync();
    })
  );

  input.addEventListener("change", sync);
  sync();
});

/* Cart rows: changing the quantity saves the row without reloading the page.

   It used to call form.requestSubmit(), which is a real form submission - the
   browser left the page, the server redirected back, and the whole cart was
   rebuilt just to change one number. The endpoint already spoke JSON; nothing
   was asking it to.

   The numbers on screen have already moved by the time this runs (the stepper
   updates them optimistically, so the click feels instant). What this adds is
   persistence, and then reconciliation: whatever the server says the quantity
   and the totals actually are wins, because it is the side that knows the
   stock. */
$$("form[data-autosubmit]").forEach((form) => {
  const input = $("input[type='number']", form);
  if (!input) return;

  const row = form.closest("[data-cart-row]") || form;
  input.dataset.saved = input.value;

  let timer = null;
  let inFlight = false;
  let again = false;

  const save = async () => {
    // One request at a time per row. A second change while the first is in
    // the air is remembered and sent after it lands, so the database ends up
    // holding the number the viewer actually stopped on.
    if (inFlight) {
      again = true;
      return;
    }
    inFlight = true;
    row.classList.add("is-saving");

    try {
      // The same helper every other fetch-posted form here uses, so the
      // "session expired, this reply is HTML not JSON" case is handled once.
      const data = await postForm(form);

      if (data.gone) {
        row.remove();
        setCartCount(data.count || 0);
        toast(data.message, "warn");
        document.dispatchEvent(new CustomEvent("mm:cart-changed"));
        return;
      }

      if (!data.ok) {
        // Put the field back to the last value the server confirmed, so the
        // page never shows a quantity the cart does not hold.
        input.value = input.dataset.saved;
        input.dispatchEvent(new Event("mm:qty", { bubbles: true }));
        toast(data.message || "Could not update the cart.", "warn");
        return;
      }

      // Server truth, applied over the optimistic figures.
      input.value = String(data.quantity);
      input.dataset.saved = String(data.quantity);
      if (data.stock) {
        input.max = String(data.stock);
        input.dataset.maxStock = String(data.stock);
      }

      const line = $("[data-line-total]", row);
      if (line) line.textContent = data.lineTotal;
      const sub = $("[data-cart-subtotal]");
      if (sub) sub.textContent = data.subtotal;
      const tot = $("[data-cart-total]");
      if (tot) tot.textContent = data.total;
      const cnt = $("[data-cart-count]");
      if (cnt) cnt.textContent = data.count + (data.count === 1 ? " item" : " items");
      setCartCount(data.count);

      // Re-run the stepper's own clamping against the refreshed max.
      input.dispatchEvent(new Event("mm:qty", { bubbles: true }));

      if (data.capped) toast(data.message, "warn");
      document.dispatchEvent(new CustomEvent("mm:cart-changed"));
    } catch (err) {
      /* The request itself failed - offline, or the session expired and the
         reply was not JSON. Hand the change to a normal form submission so it
         is not quietly lost; the page reloads, which is the old behaviour and
         the correct last resort. */
      form.submit();
      return;
    } finally {
      inFlight = false;
      row.classList.remove("is-saving");
      if (again) {
        again = false;
        save();
      }
    }
  };

  input.addEventListener("mm:qty", () => {
    if (input.value === input.dataset.saved) return;
    // Held briefly so holding down "+" is one request, not one per click.
    clearTimeout(timer);
    timer = setTimeout(save, 280);
  });

  // With scripting on, the visible submit button is redundant; the form still
  // works without it because the server handles the plain POST as well.
  form.addEventListener("submit", (e) => {
    if (e.submitter && e.submitter.type === "submit") return; // genuine submit
    e.preventDefault();
  });
});

/* ------------------------------------------------- image picker component */
/* One drop zone, used by the design studio and by the shape/design forms in
   the back office. It finds everything it needs inside itself, so a page can
   have more than one and nothing is wired to a particular element id.

   The checks here are for the person choosing the file - they say "that will
   not work" before a megabyte is uploaded. They are not the gate: the same
   extension, size, MIME and content checks run again in PHP, and dropping a
   file goes through exactly the same input as clicking, so there is no path
   that reaches the server unvalidated. */
$$("[data-dropzone]").forEach((zone) => {
  const input = $("[data-dz-input]", zone);
  if (!input) return;

  const preview = $("[data-dz-preview]", zone);
  const thumb = $("[data-dz-thumb]", zone);
  const nameEl = $("[data-dz-name]", zone);
  const errEl = $("[data-dz-error]", zone);
  const clear = $("[data-dz-clear]", zone);
  const drop = $(".dropzone", zone) || zone;

  const maxBytes = parseInt(zone.dataset.maxBytes, 10) || 0;
  const accept = (zone.dataset.accept || "")
    .split(",")
    .map((x) => x.trim().toLowerCase())
    .filter(Boolean);

  let objectUrl = null;

  const say = (message) => {
    if (!errEl) return;
    errEl.textContent = message || "";
    errEl.hidden = !message;
    zone.classList.toggle("has-error", !!message);
  };

  const readable = (bytes) =>
    bytes >= 1048576
      ? (bytes / 1048576).toFixed(1) + " MB"
      : Math.max(1, Math.round(bytes / 1024)) + " KB";

  const reset = () => {
    input.value = "";
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = null;
    }
    preview?.classList.remove("show");
    say("");
    zone.dispatchEvent(new CustomEvent("mm:file", { detail: null, bubbles: true }));
  };

  const show = (file) => {
    if (!file) return;

    const ext = (file.name.split(".").pop() || "").toLowerCase();
    if (accept.length && !accept.includes(ext)) {
      reset();
      say(
        ext === "svg" || ext === "svgz"
          ? "SVG files are not accepted, because they can contain scripts. Choose a JPG, PNG or WEBP."
          : "“" + ext.toUpperCase() + "” files are not supported. Choose a " +
            accept.join(", ").toUpperCase() + " image."
      );
      return;
    }
    if (maxBytes && file.size > maxBytes) {
      reset();
      say("That image is " + readable(file.size) + ". The limit is " + readable(maxBytes) + ".");
      return;
    }

    say("");
    if (objectUrl) URL.revokeObjectURL(objectUrl);
    objectUrl = URL.createObjectURL(file);
    if (thumb) {
      thumb.src = objectUrl;
      thumb.alt = "Preview of " + file.name;
    }
    if (nameEl) nameEl.textContent = file.name + " · " + readable(file.size);
    preview?.classList.add("show");

    // Let the page react (the studio paints it onto the bookmark preview).
    zone.dispatchEvent(new CustomEvent("mm:file", { detail: file, bubbles: true }));
  };

  input.addEventListener("change", () => show(input.files[0]));

  clear?.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    reset();
    input.focus();
  });

  ["dragenter", "dragover"].forEach((evt) =>
    drop.addEventListener(evt, (e) => {
      e.preventDefault();
      drop.classList.add("is-drag");
    })
  );
  ["dragleave", "drop"].forEach((evt) =>
    drop.addEventListener(evt, (e) => {
      e.preventDefault();
      drop.classList.remove("is-drag");
    })
  );
  drop.addEventListener("drop", (e) => {
    const files = e.dataTransfer?.files;
    if (!files || !files.length) return;
    // Assigned to the input itself, so the form posts it exactly as if it had
    // been picked from the dialog - one code path, one set of server checks.
    input.files = files;
    show(files[0]);
  });

  /* The form is posting: say so, and stop a second submission creating two
     records from one upload. */
  const form = input.form;
  if (form) {
    form.addEventListener("submit", () => {
      if (!input.files.length && !form.dataset.dzEditing) return;
      zone.classList.add("is-uploading");
      $$("button[type='submit']", form).forEach((b) => {
        b.disabled = true;
        b.dataset.busyLabel = b.textContent;
        b.textContent = "Uploading…";
      });
    });
  }
});

/* ---------------------------------------------------- product image gallery */

$$("[data-gallery]").forEach((gallery) => {
  const main = $("[data-gallery-main]", gallery);
  if (!main) return;

  $$("[data-gallery-thumb]", gallery).forEach((thumb) => {
    thumb.addEventListener("click", () => {
      main.src = thumb.dataset.galleryThumb;
      main.alt = thumb.dataset.galleryAlt || main.alt;
      $$("[data-gallery-thumb]", gallery).forEach((t) => {
        t.classList.toggle("is-active", t === thumb);
        t.setAttribute("aria-pressed", String(t === thumb));
      });
    });
  });
});

/* ======================================================================
   Customization studio — every control feeds one live preview.
   ====================================================================== */

const studio = $("#studio");

if (studio) {
  const preview = $("#bmPreview");
  const bmPattern = $("#bmPattern");
  const bmPhoto = $("#bmPhoto");
  const bmText = $("#bmText");
  const textPlaceholder = bmText?.dataset.placeholder || "Your text appears here";

  const setSummary = (key, value) => {
    const el = $(`[data-summary="${key}"]`);
    if (el) el.textContent = value;
  };

  /* --- shape + design thumbnails --- */
  const syncGroup = (group) => {
    $$(`.option-thumb input[name="${group}"]`).forEach((radio) => {
      radio.closest(".option-thumb")?.classList.toggle("selected", radio.checked);
    });
  };

  const applyShape = (radio) => {
    if (!preview) return;
    preview.dataset.shape = radio.dataset.shape || "classic";
    setSummary("shape", radio.dataset.name || "—");
  };

  const applyDesign = (radio) => {
    if (bmPattern && radio.dataset.img) {
      bmPattern.style.setProperty("--bm-pattern", `url("${radio.dataset.img}")`);
    }
    setSummary("design", radio.dataset.name || "—");
  };

  $$('.option-thumb input[name="shape_id"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      syncGroup("shape_id");
      applyShape(radio);
    });
    if (radio.checked) applyShape(radio);
  });

  $$('.option-thumb input[name="design_id"]').forEach((radio) => {
    radio.addEventListener("change", () => {
      syncGroup("design_id");
      applyDesign(radio);
    });
    if (radio.checked) applyDesign(radio);
  });

  syncGroup("shape_id");
  syncGroup("design_id");

  /* --- preview colour: picker, hex and RGB, all one value ---------------
     Four controls edit the same colour, so each one writes through a single
     apply() and the others are re-synced from it. Without that they drift. */
  const colorBox = $("[data-color-picker]");

  if (colorBox && preview) {
    const well = $("[data-color-well]", colorBox);
    const hexInput = $("[data-color-hex]", colorBox);
    const numFor = (ch) => $(`[data-rgb="${ch}"]`, colorBox);
    const rangeFor = (ch) => $(`[data-rgb-range="${ch}"]`, colorBox);

    const clamp = (n) => Math.max(0, Math.min(255, Number.isFinite(n) ? n : 0));
    const toHex = (r, g, b) =>
      "#" + [r, g, b].map((n) => clamp(Math.round(n)).toString(16).padStart(2, "0")).join("");

    const parseHex = (value) => {
      const m = String(value).trim().replace(/^#/, "");
      if (!/^[0-9a-f]{6}$/i.test(m)) return null;
      return [
        parseInt(m.slice(0, 2), 16),
        parseInt(m.slice(2, 4), 16),
        parseInt(m.slice(4, 6), 16),
      ];
    };

    // Keep the bookmark's text legible whatever colour is chosen.
    const readableInk = (r, g, b) =>
      (0.299 * r + 0.587 * g + 0.114 * b) / 255 > 0.62 ? "#1f1d1a" : "#ffffff";

    const apply = (r, g, b, { skipWell = false, skipHex = false } = {}) => {
      r = clamp(r); g = clamp(g); b = clamp(b);
      const hex = toHex(r, g, b);

      preview.style.backgroundColor = hex;
      preview.style.setProperty("--bm-ink", readableInk(r, g, b));

      if (!skipWell && well) well.value = hex;
      if (!skipHex && hexInput) hexInput.value = hex.toUpperCase();

      ["r", "g", "b"].forEach((ch, i) => {
        const v = [r, g, b][i];
        const num = numFor(ch);
        const range = rangeFor(ch);
        if (num && num.value !== String(v)) num.value = String(v);
        if (range && range.value !== String(v)) range.value = String(v);
      });
    };

    const currentRGB = () => ["r", "g", "b"].map((ch) => clamp(parseInt(numFor(ch)?.value, 10)));

    well?.addEventListener("input", () => {
      const rgb = parseHex(well.value);
      if (rgb) apply(...rgb, { skipWell: true });
    });

    hexInput?.addEventListener("input", () => {
      const rgb = parseHex(hexInput.value);
      if (rgb) apply(...rgb, { skipHex: true });
    });
    // Restore a valid value if the field is left mid-edit.
    hexInput?.addEventListener("blur", () => {
      if (!parseHex(hexInput.value)) apply(...currentRGB());
    });

    ["r", "g", "b"].forEach((ch) => {
      numFor(ch)?.addEventListener("input", () => apply(...currentRGB()));
      rangeFor(ch)?.addEventListener("input", () => {
        const num = numFor(ch);
        if (num) num.value = rangeFor(ch).value;
        apply(...currentRGB());
      });
    });

    $$("[data-color-preset]", colorBox).forEach((btn) => {
      btn.addEventListener("click", () => {
        const rgb = parseHex(btn.dataset.colorPreset);
        if (rgb) apply(...rgb);
      });
    });

    apply(...currentRGB());
  }

  /* --- photo upload --- */
  /* The picker itself is the shared [data-dropzone] component. All the studio
     adds is what the chosen photo means here: it goes onto the bookmark
     preview and into the summary line. */
  const fileInput = $("#custom_image");

  fileInput?.closest("[data-dropzone]")?.addEventListener("mm:file", (e) => {
    const file = e.detail;
    if (!file) {
      bmPhoto?.classList.remove("show");
      setSummary("photo", "None");
      return;
    }
    if (bmPhoto) {
      bmPhoto.src = URL.createObjectURL(file);
      bmPhoto.classList.add("show");
    }
    setSummary("photo", file.name);
  });



  /* --- personalized text --- */
  const textInput = $("#custom_text");
  const charCount = $("#charCount");

  const syncText = () => {
    const value = textInput.value.trim();
    if (bmText) {
      bmText.textContent = value || textPlaceholder;
      bmText.classList.toggle("is-placeholder", value === "");
    }
    if (charCount) {
      charCount.textContent = textInput.value.length + " / " + textInput.maxLength + " characters";
    }
    setSummary("text", value || "None");
  };

  textInput?.addEventListener("input", syncText);
  if (textInput) syncText();
}

/* ------------------------------------------------------- payment selection */

$$(".pay-option input").forEach((radio) => {
  const sync = () =>
    $$(`.pay-option input[name="${radio.name}"]`).forEach((r) =>
      r.closest(".pay-option")?.classList.toggle("selected", r.checked)
    );
  radio.addEventListener("change", sync);
  if (radio.checked) sync();
});

/* ----------------------------------------------- guard against double submit */

$$("form[data-once]").forEach((form) => {
  form.addEventListener("submit", () => {
    const btn = $("button[type='submit']", form);
    if (!btn || form.dataset.submitted) return;
    form.dataset.submitted = "1";
    btn.disabled = true;
    btn.textContent = btn.dataset.busy || "Working…";
  });
});

/* ---------------------------------------------- checkout progress indicator
   Reflects which section the shopper is actually looking at — the steps are
   sections of one page, so the marker follows the viewport rather than faking
   a multi-page wizard. */

const checkoutSteps = $("#checkoutSteps");

if (checkoutSteps && "IntersectionObserver" in window) {
  const markers = $$("[data-step-for]", checkoutSteps);
  const sections = markers
    .map((m) => document.getElementById(m.dataset.stepFor))
    .filter(Boolean);

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const index = sections.indexOf(entry.target);
        markers.forEach((marker, i) => {
          marker.classList.toggle("is-active", i === index);
          marker.classList.toggle("is-done", i < index);
        });
      });
    },
    { rootMargin: "-25% 0px -60% 0px" }
  );

  sections.forEach((section) => observer.observe(section));
}

/* ----------------------------------------------------------------- theme
   The stored preference is already applied to <html> by includes/theme_boot.php
   before first paint. This only handles switching it afterwards, and keeping
   every toggle on the page (and any other open tab) in agreement. */
const THEME_KEY = "markme-theme";

const readTheme = () => {
  try {
    const saved = localStorage.getItem(THEME_KEY);
    return saved === "dark" || saved === "light" ? saved : "light";
  } catch (err) {
    return "light";
  }
};

const paintToggles = (theme) => {
  $$("[data-theme-option]").forEach((btn) => {
    btn.setAttribute("aria-pressed", String(btn.dataset.themeOption === theme));
  });
  const meta = $('meta[name="theme-color"]');
  if (meta) meta.setAttribute("content", theme === "dark" ? "#15130f" : "#fbf8f3");
};

let themeAnimTimer;

const applyTheme = (theme, persist = true) => {
  const root = document.documentElement;

  // Turn transitions on only for the length of the swap. Leaving them on
  // permanently would animate every hover; having them on at page load would
  // animate the saved theme into place, which is the flash we are avoiding.
  root.classList.add("theme-anim");
  clearTimeout(themeAnimTimer);
  themeAnimTimer = setTimeout(() => root.classList.remove("theme-anim"), 400);

  root.setAttribute("data-theme", theme);

  if (persist) {
    try {
      localStorage.setItem(THEME_KEY, theme);
    } catch (err) {
      /* Storage unavailable — the choice still applies for this page view. */
    }
  }
  paintToggles(theme);
};

if ($$("[data-theme-option]").length) {
  paintToggles(readTheme());
  $$("[data-theme-option]").forEach((btn) => {
    btn.addEventListener("click", () => applyTheme(btn.dataset.themeOption));
  });
}

/* Another tab changed the preference — follow it. */
window.addEventListener("storage", (e) => {
  if (e.key === THEME_KEY && (e.newValue === "dark" || e.newValue === "light")) {
    applyTheme(e.newValue, false);
  }
});

/* -------------------------------------------------------- avatar preview
   Show the chosen profile picture immediately, so the customer can tell they
   picked the right file before submitting the form. */
const avatarInput = $("[data-avatar-input]");
const avatarPreview = $("[data-avatar-preview]");

if (avatarInput && avatarPreview) {
  avatarInput.addEventListener("change", () => {
    const file = avatarInput.files && avatarInput.files[0];
    if (!file || !file.type.startsWith("image/")) return;

    const url = URL.createObjectURL(file);
    avatarPreview.innerHTML = "";
    const img = document.createElement("img");
    img.src = url;
    img.alt = "Your new profile picture";
    img.addEventListener("load", () => URL.revokeObjectURL(url), { once: true });
    avatarPreview.appendChild(img);
  });
}

/* ======================================================================
   Customer flow: toasts, cart drawer, shop filtering, carousel, scrollspy
   ---------------------------------------------------------------------
   Everything here is progressive enhancement. Each interaction replaces a
   form submit or a link that still works with JavaScript disabled, so the
   PHP endpoints remain the only place a change is actually authorised.
   ====================================================================== */

const BASE = document.documentElement.dataset.base || "";

/* ------------------------------------------------------------- toasts */
const toastRegion = $("#toastRegion");

const toast = (message, kind = "ok") => {
  if (!toastRegion || !message) return;

  const el = document.createElement("div");
  el.className = "toast-item is-" + kind;
  el.textContent = message;
  toastRegion.appendChild(el);

  // Let the element land before transitioning, so it animates in.
  requestAnimationFrame(() => el.classList.add("is-in"));

  const remove = () => {
    el.classList.remove("is-in");
    el.addEventListener("transitionend", () => el.remove(), { once: true });
    // Fallback if the transition never fires (reduced motion, background tab).
    setTimeout(() => el.remove(), 400);
  };
  setTimeout(remove, 3000);
};

/** POST a form through fetch, returning the parsed JSON payload. */
const postForm = async (form) => {
  const res = await fetch(form.action, {
    method: "POST",
    body: new FormData(form),
    headers: { "X-Requested-With": "fetch" },
    credentials: "same-origin",
  });

  // A guard that redirected (e.g. the session expired) gives HTML, not JSON.
  const type = res.headers.get("content-type") || "";
  if (!type.includes("application/json")) {
    throw new Error("not-json");
  }
  return res.json();
};

/* -------------------------------------------------------- cart drawer */
const cartDrawer = $("#cartDrawer");
const cartScrim = $("#cartScrim");
const cartDrawerBody = $("#cartDrawerBody");
let lastCartTrigger = null;

const setCartCount = (count) => {
  const link = $(".cart-link");
  if (!link) return;

  let badge = $(".cart-count", link);
  if (count > 0) {
    if (!badge) {
      badge = document.createElement("span");
      badge.className = "cart-count";
      badge.setAttribute("aria-hidden", "true");
      link.appendChild(badge);
    }
    badge.textContent = count > 99 ? "99+" : String(count);
  } else if (badge) {
    badge.remove();
  }
  link.setAttribute(
    "aria-label",
    count ? "Cart, " + count + " item" + (count === 1 ? "" : "s") : "Cart, empty"
  );
};

const loadCartDrawer = async () => {
  if (!cartDrawerBody) return;
  cartDrawerBody.setAttribute("aria-busy", "true");
  try {
    const res = await fetch(BASE + "/customer/cart_partial.php", {
      headers: { "X-Requested-With": "fetch" },
      credentials: "same-origin",
    });
    cartDrawerBody.innerHTML = await res.text();
  } catch (err) {
    cartDrawerBody.innerHTML =
      '<p class="cart-drawer-loading">Could not load your cart. ' +
      '<a href="' + BASE + '/customer/cart.php">Open the cart page</a>.</p>';
  }
  cartDrawerBody.setAttribute("aria-busy", "false");
};

const openCartDrawer = async (trigger) => {
  if (!cartDrawer) return;
  lastCartTrigger = trigger || document.activeElement;

  cartDrawer.hidden = false;
  cartScrim.hidden = false;
  requestAnimationFrame(() => {
    cartDrawer.classList.add("is-open");
    cartScrim.classList.add("is-open");
  });
  cartDrawer.setAttribute("aria-hidden", "false");
  document.body.classList.add("no-scroll");

  await loadCartDrawer();
  const close = $("[data-cart-close]", cartDrawer);
  if (close) close.focus();
};

const closeCartDrawer = () => {
  if (!cartDrawer || cartDrawer.hidden) return;

  cartDrawer.classList.remove("is-open");
  cartScrim.classList.remove("is-open");
  cartDrawer.setAttribute("aria-hidden", "true");
  document.body.classList.remove("no-scroll");

  setTimeout(() => {
    cartDrawer.hidden = true;
    cartScrim.hidden = true;
  }, 240);

  if (lastCartTrigger && document.contains(lastCartTrigger)) lastCartTrigger.focus();
};

if (cartDrawer) {
  cartScrim.addEventListener("click", closeCartDrawer);

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !cartDrawer.hidden) closeCartDrawer();
  });

  // Keep focus inside the drawer while it is open.
  cartDrawer.addEventListener("keydown", (e) => {
    if (e.key !== "Tab") return;
    const focusable = $$(
      'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])',
      cartDrawer
    ).filter((el) => el.offsetParent !== null);
    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });

  // The header cart icon opens the drawer instead of navigating.
  const cartLink = $(".cart-link");
  if (cartLink) {
    cartLink.addEventListener("click", (e) => {
      e.preventDefault();
      openCartDrawer(cartLink);
    });
  }

  // Delegated, because the drawer's contents are replaced after every change.
  document.addEventListener("click", (e) => {
    const closer = e.target.closest("[data-cart-close]");
    if (closer && cartDrawer.contains(closer) && closer.tagName !== "A") {
      e.preventDefault();
      closeCartDrawer();
    }
  });

  // Quantity / remove inside the drawer.
  document.addEventListener("submit", async (e) => {
    const form = e.target;
    if (!cartDrawer.contains(form)) return;
    if (!form.matches("[data-cart-qty], [data-cart-remove]")) return;

    e.preventDefault();
    try {
      const data = await postForm(form);
      toast(data.message, data.ok ? "ok" : "warn");
      if (typeof data.count === "number") setCartCount(data.count);
      await loadCartDrawer();
    } catch (err) {
      form.submit(); // Session gone or server unhappy: let the normal POST run.
    }
  });

  document.addEventListener("change", (e) => {
    const input = e.target;
    if (!cartDrawer.contains(input) || input.name !== "quantity") return;
    const form = input.closest("[data-cart-qty]");
    if (!form) return;
    if (form.requestSubmit) {
      form.requestSubmit();
    } else {
      form.dispatchEvent(new Event("submit", { cancelable: true, bubbles: true }));
    }
  });
}

/* Add to cart from a product card or the studio, without leaving the page. */
document.addEventListener("submit", async (e) => {
  const form = e.target;
  if (!form.matches("[data-cart-add]")) return;
  if (cartDrawer && cartDrawer.contains(form)) return;

  // A customization that carries a file posts normally: the studio is a
  // full-page flow and the upload deserves real navigation feedback.
  const fileInput = form.querySelector('input[type="file"]');
  if (fileInput && fileInput.files && fileInput.files.length) return;

  e.preventDefault();
  const btn = form.querySelector('button[type="submit"]');
  if (btn) btn.disabled = true;

  try {
    const data = await postForm(form);
    if (data.ok) {
      toast(data.message || "Added to cart");
      if (typeof data.count === "number") setCartCount(data.count);
      await openCartDrawer(btn);
    } else {
      toast(data.message || "Could not add that item", "warn");
    }
  } catch (err) {
    form.submit();
    return;
  } finally {
    if (btn) btn.disabled = false;
  }
});

/* ------------------------------------------- login-required prompt copy */
const gateModal = $("#loginRequiredModal");
if (gateModal) {
  const gateMessage = $("[data-gate-message]", gateModal);
  const reasons = {
    customize: "Please log in or create an account to customize your bookmark.",
    cart: "Please log in or create an account to add items to your cart.",
    design: "Please log in or create an account to save your design.",
  };
  document.addEventListener("click", (e) => {
    const trigger = e.target.closest("[data-login-reason]");
    if (trigger && gateMessage) {
      gateMessage.textContent = reasons[trigger.dataset.loginReason] || reasons.cart;
    }
  });
}

/* ----------------------------------------------------------- carousel */
/* Shows a fixed number of whole cards per view and steps one card at a time,
   rather than rendering one long row. The storefront and the back office use
   the same component; only the card and the per-view count differ, and both
   come from the markup so there is one implementation to keep working. */
const initCarousel = (root) => {
  const track = $("[data-carousel-track]", root);
  const prev = $("[data-carousel-prev]", root);
  const next = $("[data-carousel-next]", root);
  if (!track) return null;

  /* Where to open. Normally the start; a screen that is editing one item
     passes its position so the selection is not paged off screen. Clamped
     against the real card count on every update(). */
  let index = Math.max(0, parseInt(root.dataset.carouselStart || "0", 10) || 0);

  // "desktop tablet phone", defaulting to the storefront's 3 / 2 / 1.
  const counts = (root.dataset.perView || "3 2 1")
    .split(/\s+/)
    .map((n) => Math.max(1, parseInt(n, 10) || 1));
  const itemSelector = root.dataset.carouselItem || ".product-card";

  /* Optional paging source. A carousel that declares data-carousel-more asks
     it for the next batch when the viewer reaches the end, rather than the
     page rendering a whole history nobody may scroll to. The endpoint answers
     204 when there is nothing older, which is how this stops asking. */
  const moreUrl = root.dataset.carouselMore || "";
  const status = root.parentElement
    ? $("[data-carousel-status]", root.parentElement)
    : null;
  let exhausted = !moreUrl;
  let loading = false;

  /* An optional floor on card width. Three width buckets cannot describe
     every panel - five cards that read well at 1440 are 115px of clipped
     text at 1024 - so a carousel may also say how narrow a card may get, and
     the count drops until they fit. */
  const minCard = parseInt(root.dataset.minCard || "0", 10) || 0;

  const perView = () => {
    const w = window.innerWidth;
    let n = counts[0];
    if (w < 640) n = counts[2] || counts[counts.length - 1];
    else if (w < 992) n = counts[1] || counts[counts.length - 1];

    if (minCard > 0) {
      const avail = track.getBoundingClientRect().width;
      const gap = parseFloat(getComputedStyle(track).columnGap || "0") || 0;
      if (avail > 0) {
        n = Math.min(n, Math.max(1, Math.floor((avail + gap) / (minCard + gap))));
      }
    }
    return n;
  };

  const cards = () => $$(itemSelector, track);

  const update = () => {
    const items = cards();
    const per = perView();
    const maxIndex = Math.max(0, items.length - per);
    index = Math.min(index, maxIndex);

    // Publish the live count so the card widths are derived from the same
    // number the stepping uses. Two sources of truth here meant a carousel
    // could show four cards and step past two.
    root.style.setProperty("--per", String(per));

    if (!items.length) {
      track.style.transform = "translateX(0)";
      root.classList.add("is-empty");
      if (prev) prev.disabled = true;
      if (next) next.disabled = true;
      return;
    }
    root.classList.remove("is-empty");

    // Measure from the live layout, so the gap never has to be hardcoded.
    const gap = parseFloat(getComputedStyle(track).columnGap || "0") || 0;
    const step = items[0].getBoundingClientRect().width + gap;
    track.style.transform = "translateX(" + -index * step + "px)";

    const atStart = index <= 0;
    const atEnd = index >= maxIndex;
    if (prev) prev.disabled = atStart;
    // At the end is only the end once the source says there is nothing older.
    if (next) next.disabled = atEnd && exhausted;
    root.classList.toggle("no-nav", items.length <= per && exhausted);

    if (status) {
      status.textContent = exhausted
        ? "All " + items.length + " recorded events loaded."
        : "Showing the latest " + items.length + " events.";
    }

    // Cards scrolled out of view must not be tab stops.
    items.forEach((card, i) => {
      const visible = i >= index && i < index + per;
      card.inert = !visible;
      card.setAttribute("aria-hidden", visible ? "false" : "true");
    });
  };

  /** Append the next batch. Failure is quiet: what is already on screen keeps
      working, and we simply stop asking for more. */
  const fetchMore = async () => {
    if (exhausted || loading) return;
    loading = true;
    track.classList.add("is-loading");
    try {
      const url = new URL(moreUrl, window.location.origin);
      url.searchParams.set("offset", String(cards().length));

      const res = await fetch(url, {
        headers: { "X-Requested-With": "fetch" },
        credentials: "same-origin",
      });

      if (res.status === 204 || !res.ok) {
        exhausted = true;
      } else {
        const html = (await res.text()).trim();
        if (html) {
          track.insertAdjacentHTML("beforeend", html);
        } else {
          exhausted = true;
        }
      }
    } catch (err) {
      exhausted = true;
    } finally {
      loading = false;
      track.classList.remove("is-loading");
      update();
    }
  };

  const go = async (delta) => {
    // Paging past the last card is what asks for the next batch, so nothing
    // beyond the first five is fetched unless someone goes looking for it.
    if (delta > 0 && !exhausted && index >= Math.max(0, cards().length - perView())) {
      await fetchMore();
    }
    const maxIndex = Math.max(0, cards().length - perView());
    index = Math.max(0, Math.min(index + delta, maxIndex));
    update();
  };

  if (prev) prev.addEventListener("click", () => go(-1));
  if (next) next.addEventListener("click", () => go(1));

  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(update, 120);
  });

  update();
  return {
    update,
    reset: () => {
      index = 0;
      update();
    },
  };
};

/* Every other carousel on the page — the back office uses this one too. The
   shop section is wired separately below because filtering has to reset it. */
$$("[data-carousel]").forEach((root) => {
  if (!root.closest("#shop")) initCarousel(root);
});

/* ------------------------------------------- shop: in-place filtering */
const shopSection = $("#shop");

if (shopSection) {
  const carousel = initCarousel(shopSection);
  const track = $("[data-carousel-track]", shopSection);
  const chips = $$("[data-filter-category]", shopSection);
  const status = $("[data-shop-status]", shopSection);

  const applyFilter = async (chip) => {
    const category = chip.dataset.filterCategory || "";

    chips.forEach((c) => {
      const active = c === chip;
      c.classList.toggle("active", active);
      c.setAttribute("aria-pressed", String(active));
    });

    track.classList.add("is-loading");
    try {
      const url = new URL(BASE + "/customer/shop_products.php", window.location.origin);
      if (category) url.searchParams.set("category", category);

      const res = await fetch(url, {
        headers: { "X-Requested-With": "fetch" },
        credentials: "same-origin",
      });
      track.innerHTML = await res.text();

      const count = $$(".product-card", track).length;
      const label = chip.textContent.trim();
      if (status) {
        status.textContent = count
          ? count + (count === 1 ? " bookmark" : " bookmarks") + " in " + label
          : "No bookmarks in " + label + " yet";
      }
      if (carousel) carousel.reset();
    } catch (err) {
      // Filtering failed: fall back to the real Shop page rather than
      // leaving the customer looking at a stale list.
      window.location.href =
        BASE + "/customer/shop.php" + (category ? "?category=" + category : "");
    } finally {
      track.classList.remove("is-loading");
    }
  };

  chips.forEach((chip) => chip.addEventListener("click", () => applyFilter(chip)));
}

/* ---------------------------------------------------------- scrollspy */
/* Highlights the nav item for whichever one-page section is in view. */
const spyLinks = $$("[data-nav-section]");

if (spyLinks.length && "IntersectionObserver" in window) {
  const sections = spyLinks
    .map((link) => document.getElementById(link.dataset.navSection))
    .filter(Boolean);

  const setActive = (id) => {
    spyLinks.forEach((link) => {
      const on = link.dataset.navSection === id;
      link.classList.toggle("active", on);
      if (on) {
        link.setAttribute("aria-current", "true");
      } else {
        link.removeAttribute("aria-current");
      }
    });
  };

  if (sections.length) {
    const seen = new Map();
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => seen.set(entry.target.id, entry));

        // The topmost section currently intersecting wins.
        const visible = Array.from(seen.values())
          .filter((entry) => entry.isIntersecting)
          .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);

        if (visible.length) setActive(visible[0].target.id);
      },
      { rootMargin: "-45% 0px -45% 0px", threshold: 0 }
    );

    sections.forEach((section) => observer.observe(section));

    // At the very top of the page, Home is the active item.
    window.addEventListener(
      "scroll",
      () => {
        if (window.scrollY < 120) setActive("top");
      },
      { passive: true }
    );
  }
}

/* ==================================================================== profile
   Edit mode, dependent province/city dropdowns, and an Enter guard.

   The server still validates everything this touches — profile.php re-checks
   the province/city pair against includes/ph_locations.php — so this is about
   making the form behave, not about deciding what is allowed.
   ==================================================================== */
const profileForm = $("[data-profile-form]");

if (profileForm) {
  const provinceSelect = $("[data-province]", profileForm);
  const citySelect = $("[data-city]", profileForm);
  const startBtn = $("[data-edit-start]", profileForm);
  const saveBtn = $("[data-edit-save]", profileForm);
  const cancelBtn = $("[data-edit-cancel]", profileForm);
  const hint = $("[data-edit-hint]", profileForm);
  const avatarPreviewEl = $("[data-avatar-preview]", profileForm);

  const fields = () =>
    $$("input, select, textarea", profileForm).filter((el) => el.type !== "hidden");

  /* ---- snapshot, so Cancel can restore exactly what was saved ---------- */
  const snapshot = new Map();
  const takeSnapshot = () => {
    snapshot.clear();
    fields().forEach((el) => {
      if (el.type === "file") return;
      snapshot.set(el, el.value);
    });
    snapshot.set("avatarHTML", avatarPreviewEl ? avatarPreviewEl.innerHTML : null);
  };

  const restoreSnapshot = () => {
    fields().forEach((el) => {
      if (el.type === "file") {
        el.value = "";
        return;
      }
      if (snapshot.has(el)) el.value = snapshot.get(el);
    });
    // The city list depends on the province, so rebuild it before restoring.
    const savedCity = snapshot.get(citySelect);
    if (provinceSelect && citySelect) {
      fillCities(provinceSelect.value, savedCity);
    }
    const avatarHTML = snapshot.get("avatarHTML");
    if (avatarPreviewEl && typeof avatarHTML === "string") {
      avatarPreviewEl.innerHTML = avatarHTML;
    }
  };

  /* ---- locked vs editing ----------------------------------------------- */
  const setLocked = (locked) => {
    profileForm.dataset.locked = String(locked);

    fields().forEach((el) => {
      if (el.tagName === "SELECT") {
        el.disabled = locked;
      } else if (el.type === "file") {
        el.disabled = locked;
      } else {
        // readOnly rather than disabled: disabled inputs are not submitted,
        // and a read-only field still reads normally to assistive tech.
        el.readOnly = locked;
      }
    });

    if (startBtn) startBtn.hidden = !locked;
    if (saveBtn) saveBtn.hidden = locked;
    if (cancelBtn) cancelBtn.hidden = locked;
    if (hint) hint.hidden = !locked;

    $$("[data-edit-only]", profileForm).forEach((el) => {
      el.style.display = locked ? "none" : "";
    });
  };

  /* ---- dependent city list --------------------------------------------- */
  const fillCities = (province, selected = "") => {
    if (!citySelect) return;
    const cities = (phLocations() || {})[province] || [];

    citySelect.innerHTML = "";
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = province ? "Select city" : "Select a province first";
    citySelect.appendChild(placeholder);

    cities.forEach((city) => {
      const opt = document.createElement("option");
      opt.value = city;
      opt.textContent = city;
      if (city === selected) opt.selected = true;
      citySelect.appendChild(opt);
    });
  };

  if (provinceSelect && citySelect) {
    provinceSelect.addEventListener("change", () => {
      // A new province invalidates the old city, so it resets rather than
      // silently keeping a city that belongs somewhere else.
      fillCities(provinceSelect.value, "");
    });
  }

  /* ---- Enter must never submit ----------------------------------------- */
  profileForm.addEventListener("keydown", (e) => {
    if (e.key !== "Enter") return;
    if (e.target.tagName === "TEXTAREA") return; // newlines are legitimate there
    // Includes the locked state and the Save button itself: Save is activated
    // by click or by Space/Enter *on the button*, which fires a click anyway.
    if (e.target.tagName === "BUTTON" || e.target.tagName === "A") return;
    e.preventDefault();
  });

  /* ---- only Save Changes submits --------------------------------------- */
  profileForm.addEventListener("submit", (e) => {
    if (profileForm.dataset.locked === "true") {
      e.preventDefault();
      return;
    }
    // A submit can only legitimately come from the Save button.
    if (e.submitter && e.submitter !== saveBtn) {
      e.preventDefault();
    }
  });

  if (startBtn) {
    startBtn.addEventListener("click", () => {
      takeSnapshot();
      setLocked(false);
      const first = $("#full_name", profileForm);
      if (first) first.focus();
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      restoreSnapshot();
      setLocked(true);
      if (startBtn) startBtn.focus();
    });
  }

  // Warn before losing edits to a page navigation.
  window.addEventListener("beforeunload", (e) => {
    if (profileForm.dataset.locked === "true") return;
    const dirty = fields().some(
      (el) => el.type !== "file" && snapshot.has(el) && snapshot.get(el) !== el.value
    );
    if (dirty) {
      e.preventDefault();
      e.returnValue = "";
    }
  });

  // The server re-renders in edit mode when validation failed, so the customer
  // keeps their work; otherwise the page opens locked.
  takeSnapshot();
  setLocked(profileForm.dataset.locked !== "false");
}

/* ============================================================ line totals
   Unit price x quantity, shown live as the stepper changes.

   Display only. place_order.php re-reads every product's price inside its
   transaction and recomputes the order total there, so nothing here is
   trusted — a tampered page can change what the customer sees, not what
   they are charged. */
const peso = (amount) =>
  "₱" + amount.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const paintLineTotal = (el, qty) => {
  const unit = parseFloat(el.dataset.unitPrice);
  if (!Number.isFinite(unit)) return;
  el.textContent = peso(unit * Math.max(1, qty || 1));
};

/* The studio: one product, one total, beside Add to cart. */
const studioTotal = $("[data-line-total]");
const studioQty = $("#qtyStudio");

if (studioTotal && studioQty) {
  const sync = () => paintLineTotal(studioTotal, parseInt(studioQty.value, 10));
  studioQty.addEventListener("mm:qty", sync);
  studioQty.addEventListener("input", sync);
  sync();
}

/* Cart rows: each line shows its own total, and the summary follows.
   The row still posts to update_cart.php, which is what actually persists
   the quantity; this only keeps the numbers honest while that is in flight. */
const cartRows = $$("[data-cart-row]");

if (cartRows.length) {
  const subtotalEl = $("[data-cart-subtotal]");
  const totalEl = $("[data-cart-total]");
  const countEl = $("[data-cart-count]");

  const recalc = () => {
    let subtotal = 0;
    let units = 0;

    cartRows.forEach((row) => {
      const qtyInput = $("input[name='quantity']", row);
      const totalCell = $("[data-line-total]", row);
      const qty = Math.max(1, parseInt(qtyInput?.value, 10) || 1);
      const unit = parseFloat(totalCell?.dataset.unitPrice);

      if (totalCell && Number.isFinite(unit)) {
        totalCell.textContent = peso(unit * qty);
        subtotal += unit * qty;
      }
      units += qty;
    });

    if (subtotalEl) subtotalEl.textContent = peso(subtotal);
    if (totalEl) totalEl.textContent = peso(subtotal);
    if (countEl) countEl.textContent = units + (units === 1 ? " item" : " items");
  };

  cartRows.forEach((row) => {
    const qtyInput = $("input[name='quantity']", row);
    if (!qtyInput) return;
    qtyInput.addEventListener("mm:qty", recalc);
    qtyInput.addEventListener("input", recalc);
  });

  recalc();
}

/* ------------------------------------------- password policy checklist */
/* Ticks the rules as they are met. This is feedback only - the identical
   list is enforced in PHP by password_policy_errors(), and a password that
   passes here can still be rejected there, never the other way round. */
$$("[data-password-policy]").forEach((list) => {
  const input = list.closest("form")?.querySelector("[data-password-input]")
    || document.querySelector("[data-password-input]");
  if (!input) return;

  const items = $$("li[data-rule]", list);

  // The rule text carries its own meaning, so the tests are matched to the
  // labels PHP rendered rather than duplicated as a second list of rules.
  const testFor = (rule) => {
    const min = parseInt(rule, 10);
    if (!Number.isNaN(min) && /characters/i.test(rule)) return (v) => v.length >= min;
    if (/uppercase/i.test(rule)) return (v) => /[A-Z]/.test(v);
    if (/lowercase/i.test(rule)) return (v) => /[a-z]/.test(v);
    if (/number/i.test(rule)) return (v) => /[0-9]/.test(v);
    if (/special/i.test(rule)) return (v) => /[^A-Za-z0-9]/.test(v);
    return () => false;
  };

  const rules = items.map((li) => ({ li, test: testFor(li.dataset.rule || "") }));

  const paint = () => {
    const value = input.value || "";
    let met = 0;
    rules.forEach(({ li, test }) => {
      const ok = test(value);
      li.classList.toggle("is-met", ok);
      met += ok ? 1 : 0;
    });
    list.classList.toggle("all-met", met === rules.length && value !== "");
  };

  input.addEventListener("input", paint);
  paint();
});

/* ------------------------------------------------------ cookie consent */
/* A single stored choice, nothing more. MarkMe sets one cookie - the session
   - so there is no tracking to switch on or off, and declining simply hides
   the notice rather than pretending to disable something. */
(() => {
  const banner = $("[data-cookie-banner]");
  if (!banner) return;

  const KEY = "markme-cookie-choice";
  let stored = null;
  try {
    stored = localStorage.getItem(KEY);
  } catch (err) {
    // Private mode or blocked storage: show the notice, take no action.
  }

  if (stored === "accepted" || stored === "declined") return;

  banner.hidden = false;

  $$("[data-cookie-choice]", banner).forEach((button) => {
    button.addEventListener("click", () => {
      try {
        localStorage.setItem(KEY, button.dataset.cookieChoice);
      } catch (err) {
        /* nothing to store into; the banner still closes for this visit */
      }
      banner.hidden = true;
    });
  });
})();

/* ---------------------------------------------------------- motion layer */
/* Reveal-on-scroll, opted into from the markup. The hidden state is only
   ever applied once this runs, so with JS off or broken the page renders
   fully visible rather than blank - the failure mode has to be "no
   animation", never "no content". */
(() => {
  const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  /* Containers that get the treatment without each template asking for it.
     Kept as one short, explicit list rather than a rule like "every .card",
     so what animates stays predictable and easy to change in one place.
     A container with several children staggers; a single one just fades. */
  const AUTO_REVEAL = [
    ".product-grid",
    ".order-card-list",
    ".cart-list",
    ".data-card",
    ".stat-row",
    ".studio-card",
    ".faq-list",
    ".summary-card",
  ];

  AUTO_REVEAL.forEach((sel) => {
    $$(sel).forEach((el) => {
      if (el.hasAttribute("data-reveal") || el.hasAttribute("data-reveal-stagger")) return;
      /* Not if something above it already animates. Two reveals over the same
         pixels means the inner one fades inside a parent that is itself
         fading - the same area composited twice, and a muddier arrival than
         either effect on its own. */
      if (el.parentElement && el.parentElement.closest("[data-reveal], [data-reveal-stagger]")) return;
      el.setAttribute(el.children.length > 1 ? "data-reveal-stagger" : "data-reveal", "");
    });
  });

  const targets = $$("[data-reveal], [data-reveal-stagger]");
  if (!targets.length) return;

  document.documentElement.classList.add("js-motion");

  if (reduced || !("IntersectionObserver" in window)) {
    targets.forEach((el) => el.classList.add("is-revealed"));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-revealed");
        // One-way: re-hiding on scroll-up is motion for its own sake.
        observer.unobserve(entry.target);
      });
    },
    { rootMargin: "0px 0px -8% 0px", threshold: 0.05 }
  );

  /* Still pending, so the sweeps below shrink to nothing as the page is read
     instead of re-measuring every target for the life of the visit. */
  let pending = targets.slice();

  const reveal = (el) => {
    el.classList.add("is-revealed");
    observer.unobserve(el);
  };

  /* Show anything the viewer could already be looking at: level with the
     bottom of the viewport or above it. Called from several places on
     purpose, because the observer alone is not enough - the page can arrive
     already scrolled (a #hash link) or have its scrolling locked by an open
     modal, and an element that never intersects would stay invisible.
     Content going missing is a far worse failure than an animation not
     playing.

     What it deliberately does NOT do is reach below the fold. Revealing
     something before it can be seen spends its animation on an empty room. */
  const revealVisible = () => {
    if (!pending.length) return;
    const fold = window.innerHeight;
    pending = pending.filter((el) => {
      const box = el.getBoundingClientRect();
      if (box.top >= fold) return true;
      reveal(el);
      return false;
    });
  };

  targets.forEach((el) => observer.observe(el));
  revealVisible();

  // After load the browser has applied any #hash scroll, so re-check.
  window.addEventListener("load", revealVisible);
  window.addEventListener("hashchange", revealVisible);

  /* A backstop for a browser whose observer never fires, throttled on a timer
     rather than run every frame. The old version swept every target on each
     scroll frame, which meant a forced layout read per element per frame and,
     worse, revealed things the instant one pixel crossed the edge - so the
     animation played just off screen and you arrived after it finished. */
  let sweepTimer = null;
  window.addEventListener(
    "scroll",
    () => {
      if (sweepTimer || !pending.length) return;
      sweepTimer = setTimeout(() => {
        sweepTimer = null;
        revealVisible();
      }, 200);
    },
    { passive: true }
  );

  /* Safety net, scoped to what is on screen. If something visible is still
     hidden after two seconds - an observer that never fired, a modal holding
     the scroll, a browser quirk nobody predicted - show it.

     It used to reveal the whole document, which quietly cancelled the feature
     it was protecting: after two seconds every section below the fold had
     already animated into an empty viewport, so anyone who read the page for
     longer than that before scrolling saw no motion at all. */
  setTimeout(revealVisible, 2000);
})();

/* --------------------------------------------------- captcha refresh */
$$("[data-captcha-reload]").forEach((button) => {
  const img = $("[data-captcha-img]", button.parentElement) || $("[data-captcha-img]");
  if (!img) return;

  button.addEventListener("click", () => {
    // A new query string, because the response is sent no-store and the
    // server issues a fresh code on every render.
    img.src = img.src.split("?")[0] + "?r=" + Date.now();
    button.classList.add("is-spinning");
    setTimeout(() => button.classList.remove("is-spinning"), 420);
  });
});

/* ------------------------------------------ server data: PH locations */
/* Read once from the inert JSON block profile.php renders. Parsed lazily so
   pages without it pay nothing, and cached so the province dropdown does not
   re-parse on every change. */
let phLocationsCache;
const phLocations = () => {
  if (phLocationsCache !== undefined) return phLocationsCache;
  const node = document.getElementById("phLocations");
  if (!node) {
    phLocationsCache = null;
    return phLocationsCache;
  }
  try {
    phLocationsCache = JSON.parse(node.textContent || "null");
  } catch (err) {
    phLocationsCache = null;
  }
  return phLocationsCache;
};

/* ------------------------------------------- password visibility toggle */
/* Applied to every password field on the site from one place, rather than
   eight templates each remembering to add a button. A field added later gets
   the behaviour for free, and there is only one implementation to keep
   accessible.

   The password is only ever read by the browser: nothing here stores it,
   sends it, or writes it anywhere. Flipping the input type is the whole
   mechanism. */
(() => {
  const fields = $$('input[type="password"]');
  if (!fields.length) return;

  // Inline SVG rather than the PHP icon set, because this markup is built
  // client-side. Same 1.75 stroke as the rest of the site's icons.
  const EYE =
    '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" ' +
    'stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M1.5 12S5 5.5 12 5.5 22.5 12 22.5 12 19 18.5 12 18.5 1.5 12 1.5 12z"/>' +
    '<circle cx="12" cy="12" r="3.2"/></svg>';
  const EYE_OFF =
    '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" ' +
    'stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
    '<path d="M9.9 5.7A9.8 9.8 0 0 1 12 5.5c7 0 10.5 6.5 10.5 6.5a17 17 0 0 1-3.3 4"/>' +
    '<path d="M6.3 7.7A17 17 0 0 0 1.5 12S5 18.5 12 18.5c1.6 0 3-.3 4.2-.8"/>' +
    '<path d="M9.8 9.9a3.2 3.2 0 0 0 4.4 4.4"/><path d="M3 3l18 18"/></svg>';

  fields.forEach((input) => {
    if (input.closest(".pw-field")) return;

    const wrap = document.createElement("div");
    wrap.className = "pw-field";
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const button = document.createElement("button");
    // type="button" matters: inside a form, a bare <button> submits it, and
    // revealing a password must never post the form.
    button.type = "button";
    button.className = "pw-toggle";
    button.innerHTML = EYE;
    button.setAttribute("aria-label", "Show password");
    button.setAttribute("aria-pressed", "false");
    // Focusable and operable from the keyboard: it is a real button, so Enter
    // and Space work without any extra key handling.
    wrap.appendChild(button);

    button.addEventListener("click", () => {
      const shown = input.type === "text";
      input.type = shown ? "password" : "text";
      button.innerHTML = shown ? EYE : EYE_OFF;
      button.setAttribute("aria-label", shown ? "Show password" : "Hide password");
      button.setAttribute("aria-pressed", String(!shown));

      // Keep the caret where it was, so revealing mid-typing is not jarring.
      const end = input.value.length;
      input.focus();
      try {
        input.setSelectionRange(end, end);
      } catch (err) {
        /* number-ish inputs can refuse setSelectionRange; harmless */
      }
    });
  });
})();

/* ----------------------------------------------- server-requested modal */
/* The server decides whether a modal should open; this decides how. Kept out
   of the template so there is one copy of the rule rather than one per page
   that happens to render the modals. */
(() => {
  const intent = $("[data-open-modal]");
  if (!intent || !window.bootstrap) return;

  // A visitor who followed a #section link came for that section. Opening the
  // welcome modal over it is an interruption, and Bootstrap sets
  // overflow:hidden on <body> while the page is already scrolled, which
  // collapses the scroll area and leaves a blank screen. The greeting yields;
  // a response to something the visitor just did does not.
  if (intent.dataset.modalReason === "greeting" && window.location.hash) return;

  const target = document.getElementById(intent.dataset.openModal);
  if (target) new bootstrap.Modal(target).show();
})();

/* ------------------------------------------- tidy the sign-out marker away */
/* The server has already acted on ?signedout=1 and the theme bootstrap has
   already cleared the stored choice, so the parameter has done its work.
   Dropping it keeps a refresh from reopening the dialog, and keeps it out of
   anything the visitor might bookmark or share. */
(() => {
  if (!/[?&]signedout=1(?:&|$)/.test(window.location.search)) return;
  if (!window.history || !history.replaceState) return;

  const url = new URL(window.location.href);
  url.searchParams.delete("signedout");
  history.replaceState(null, "", url.pathname + url.search + url.hash);
})();

/* ------------------------------------------------- MFA enrolment QR code */
/* Drawn in the browser on purpose: the otpauth URI contains the shared
   secret, so it must never be sent to an external QR image service. */
(() => {
  const holder = $("#mfaQr[data-otpauth]");
  if (!holder || typeof QRCode === "undefined") return;

  new QRCode(holder, {
    text: holder.dataset.otpauth,
    width: 168,
    height: 168,
    correctLevel: QRCode.CorrectLevel.M,
  });
})();

/* ------------------------------------------------- confirmation dialog */
/* One dialog for every destructive action. A form opts in with data-confirm
   and describes itself through data-confirm-* attributes; nothing here knows
   what a product or a category is.

   The deletion itself stays a plain form POST. If this script never runs the
   form submits as normal - the confirmation is the enhancement, not the
   delete, so a JS failure can never make a destructive action unreachable
   (or, worse, fire without asking). */
(() => {
  const dialog = $("[data-confirm-dialog]");
  const backdrop = $("[data-confirm-backdrop]");
  if (!dialog || !backdrop) return;

  const titleEl = $("[data-confirm-title]", dialog);
  const bodyEl = $("[data-confirm-body]", dialog);
  const noteEl = $("[data-confirm-note]", dialog);
  const okBtn = $("[data-confirm-ok]", dialog);
  const cancelBtn = $("[data-confirm-cancel]", dialog);

  let pendingForm = null;
  let lastFocused = null;

  const close = () => {
    dialog.hidden = true;
    backdrop.hidden = true;
    document.body.classList.remove("confirm-open");
    pendingForm = null;
    if (lastFocused && lastFocused.focus) lastFocused.focus();
  };

  const open = (form) => {
    pendingForm = form;
    lastFocused = document.activeElement;

    const d = form.dataset;
    titleEl.textContent = d.confirmTitle || "Are you sure?";
    bodyEl.textContent = d.confirmBody || "";
    bodyEl.hidden = !d.confirmBody;

    noteEl.textContent = d.confirmNote || "This action cannot be undone.";
    noteEl.hidden = d.confirmNote === "";

    okBtn.textContent = d.confirmAction || "Delete";
    okBtn.className = "btn " + (d.confirmTone === "default" ? "btn-primary" : "btn-danger");

    backdrop.hidden = false;
    dialog.hidden = false;
    document.body.classList.add("confirm-open");

    // Cancel takes focus, never the destructive button.
    cancelBtn.focus();
  };

  // Delegated, so forms rendered later (a cart drawer refresh, an appended
  // carousel card) are covered without rebinding anything.
  document.addEventListener("submit", (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement) || !form.hasAttribute("data-confirm")) return;
    if (form.dataset.confirmed === "yes") return;   // second pass, let it go
    e.preventDefault();
    open(form);
  });

  okBtn.addEventListener("click", () => {
    if (!pendingForm) return;
    const form = pendingForm;
    form.dataset.confirmed = "yes";
    close();
    // requestSubmit keeps any submit button's name/value, which a bare
    // submit() would drop.
    if (form.requestSubmit) form.requestSubmit();
    else form.submit();
  });

  cancelBtn.addEventListener("click", close);
  backdrop.addEventListener("click", close);

  document.addEventListener("keydown", (e) => {
    if (dialog.hidden) return;
    if (e.key === "Escape") {
      e.preventDefault();
      close();
      return;
    }
    if (e.key !== "Tab") return;

    // Keep focus inside the dialog while it is open.
    const focusable = [cancelBtn, okBtn];
    const first = focusable[0];
    const last = focusable[focusable.length - 1];
    if (e.shiftKey && document.activeElement === first) {
      e.preventDefault();
      last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
      e.preventDefault();
      first.focus();
    }
  });
})();

/* ------------------------------------------------- hero entrance */
/* Runs once, on the page the visitor lands on. Deliberately not a reveal:
   the hero is already in view, so it should arrive as the page settles
   rather than wait for a scroll that never comes. */
(() => {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
  const hero = $(".hero");
  if (!hero) return;
  hero.classList.add("hero-enter");
  // Removed once it has played, so a later reflow cannot replay it.
  setTimeout(() => hero.classList.remove("hero-enter"), 1400);
})();

/* ---------------------------------------------- images fade in */
/* Pairs with lazy loading: a lazily fetched thumbnail otherwise snaps in
   at full opacity the moment it decodes. Anything already cached is marked
   loaded immediately, so this never delays a paint. */
(() => {
  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

  const watch = (img) => {
    if (img.dataset.fade === "on") return;
    img.dataset.fade = "on";

    // Already decoded, from the cache or the same page load: there is nothing
    // to fade in, and animating it would be a flicker rather than a fade.
    if (img.complete && img.naturalWidth > 0) return;

    /* The class goes on when the pixels arrive, never before. Nothing in the
       stylesheet hides an image while it waits, so an image whose load event
       never comes stays visible instead of disappearing. */
    img.addEventListener("load", () => img.classList.add("img-fade"), { once: true });
  };

  $$("img").forEach(watch);

  // Cards arriving later (carousel batches, filtered grids) get it too.
  if (!("MutationObserver" in window)) return;
  new MutationObserver((records) => {
    records.forEach((r) => {
      r.addedNodes.forEach((node) => {
        if (node.nodeType !== 1) return;
        if (node.tagName === "IMG") watch(node);
        else node.querySelectorAll?.("img").forEach(watch);
      });
    });
  }).observe(document.body, { childList: true, subtree: true });
})();
