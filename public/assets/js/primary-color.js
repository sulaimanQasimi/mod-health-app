/**
 * Primary color picker for the template customizer.
 * Stores the choice and applies it app-wide via CSS variables + overrides.
 */
'use strict';

window.AppPrimaryColor = (function () {
  var COLORS = [
    { id: 'default', hex: '#696cff', labelKey: 'default' },
    { id: 'raspberry', hex: '#e30b5c', labelKey: 'raspberry' },
    { id: 'ocean', hex: '#00cfe8', labelKey: 'ocean' },
    { id: 'forest', hex: '#28c76f', labelKey: 'forest' },
    { id: 'amber', hex: '#ff9f43', labelKey: 'amber' },
    { id: 'blue', hex: '#2092ec', labelKey: 'blue' },
    { id: 'violet', hex: '#9c27b0', labelKey: 'violet' },
    { id: 'slate', hex: '#4b465c', labelKey: 'slate' }
  ];

  var mounted = false;

  function storageKey() {
    var template = document.documentElement.getAttribute('data-template') || 'vertical-menu-template';
    return 'templateCustomizer-' + template + '--PrimaryColor';
  }

  function labels() {
    return window.__appPrimaryColorLabels || {};
  }

  function colorLabel(color) {
    return labels()[color.labelKey] || color.id;
  }

  function hexToRgbTriplet(hex) {
    hex = String(hex).replace('#', '');
    return [
      parseInt(hex.slice(0, 2), 16),
      parseInt(hex.slice(2, 4), 16),
      parseInt(hex.slice(4, 6), 16)
    ].join(', ');
  }

  function mixHex(hex, towardWhite, amount) {
    var h = String(hex).replace('#', '');
    var r = parseInt(h.slice(0, 2), 16);
    var g = parseInt(h.slice(2, 4), 16);
    var b = parseInt(h.slice(4, 6), 16);
    var t = towardWhite ? 255 : 0;
    r = Math.round(r + (t - r) * amount);
    g = Math.round(g + (t - g) * amount);
    b = Math.round(b + (t - b) * amount);
    return (
      '#' +
      [r, g, b]
        .map(function (v) {
          var s = Math.max(0, Math.min(255, v)).toString(16);
          return s.length === 1 ? '0' + s : s;
        })
        .join('')
    );
  }

  function getSelected() {
    try {
      var saved = localStorage.getItem(storageKey());
      if (saved) {
        for (var i = 0; i < COLORS.length; i++) {
          if (COLORS[i].id === saved || COLORS[i].hex.toLowerCase() === saved.toLowerCase()) {
            return COLORS[i];
          }
        }
      }
    } catch (e) {}
    return COLORS[0];
  }

  function setSelected(colorId) {
    var color = null;
    for (var i = 0; i < COLORS.length; i++) {
      if (COLORS[i].id === colorId) {
        color = COLORS[i];
        break;
      }
    }
    if (!color) return;
    try {
      localStorage.setItem(storageKey(), color.id);
    } catch (e) {}
    apply(color);
    syncUi(color.id);
  }

  function ensureStyleEl() {
    var el = document.getElementById('app-primary-color-overrides');
    if (!el) {
      el = document.createElement('style');
      el.id = 'app-primary-color-overrides';
      document.head.appendChild(el);
    }
    return el;
  }

  function buildOverrides(hex) {
    var soft = mixHex(hex, true, 0.84);
    var soft2 = mixHex(hex, true, 0.78);
    var dark = mixHex(hex, false, 0.1);
    var darker = mixHex(hex, false, 0.18);
    var rgb = hexToRgbTriplet(hex);

    return [
      ':root{',
      '--bs-primary:' + hex + ';',
      '--bs-primary-rgb:' + rgb + ';',
      '--app-primary:' + hex + ';',
      '--app-primary-rgb:' + rgb + ';',
      '--app-primary-soft:' + soft + ';',
      '--app-primary-soft-2:' + soft2 + ';',
      '--app-primary-dark:' + dark + ';',
      '--app-primary-darker:' + darker + ';',
      '}',
      '.text-primary,.btn-link,.page-link,',
      '.dropdown-item.active,.dropdown-item:active{color:var(--app-primary)!important;}',
      '.bg-primary,.badge.bg-primary,.progress-bar,.list-group-item.active,',
      '.nav-pills .nav-link.active,.page-item.active .page-link,',
      '.form-check-input:checked{background-color:var(--app-primary)!important;border-color:var(--app-primary)!important;}',
      '.border-primary,.form-control:focus,.form-select:focus,.form-check-input:focus{border-color:var(--app-primary)!important;}',
      '.btn-primary{background-color:var(--app-primary)!important;border-color:var(--app-primary)!important;box-shadow:0 .125rem .25rem rgba(var(--app-primary-rgb),.4)!important;}',
      '.btn-primary:hover,.btn-primary:focus,.btn-primary:active,.btn-primary.active,',
      '.btn-check:checked+.btn-primary{background-color:var(--app-primary-dark)!important;border-color:var(--app-primary-dark)!important;}',
      '.btn-outline-primary{color:var(--app-primary)!important;border-color:var(--app-primary)!important;}',
      '.btn-outline-primary:hover,.btn-outline-primary:focus,.btn-outline-primary:active,',
      '.btn-check:checked+.btn-outline-primary{background-color:var(--app-primary)!important;border-color:var(--app-primary)!important;color:#fff!important;}',
      '.btn-label-primary{background:var(--app-primary-soft)!important;color:var(--app-primary)!important;border:0!important;}',
      '.btn-label-primary:hover,.btn-label-primary:focus{background:var(--app-primary-soft-2)!important;color:var(--app-primary-dark)!important;}',
      '.bg-label-primary{background-color:var(--app-primary-soft)!important;color:var(--app-primary)!important;}',
      '.alert-primary{background-color:var(--app-primary-soft-2)!important;border-color:var(--app-primary-soft)!important;color:var(--app-primary-dark)!important;}',
      '.table-primary{--bs-table-bg:var(--app-primary-soft)!important;--bs-table-color:var(--app-primary-darker)!important;}',
      '.layout-menu .menu-inner>.menu-item.active>.menu-link:not(.menu-toggle),',
      '.layout-menu .menu-inner>.menu-item.active>.menu-link.menu-toggle,',
      '.bg-menu-theme .menu-inner>.menu-item.active>.menu-link{color:var(--app-primary)!important;}',
      '.layout-wrapper:not(.layout-horizontal) .bg-menu-theme .menu-inner>.menu-item.active:before{background:var(--app-primary)!important;}',
      '.bg-menu-theme .menu-sub>.menu-item.active>.menu-link:not(.menu-toggle):before,',
      '.bg-menu-theme .menu-sub>.menu-item.active>.menu-link.menu-toggle:before{background:var(--app-primary)!important;border-color:var(--app-primary)!important;}',
      '.template-customizer-open-btn{background-color:var(--app-primary)!important;}',
      '.footer-link:hover,.navbar.bg-primary{color:var(--app-primary)!important;}',
      'html:not([data-primary-color="#696cff"]) .menu-vertical .menu-item.active>.menu-link:not(.menu-toggle){background-color:rgba(var(--app-primary-rgb),.16)!important;}'
    ].join('');
  }

  function apply(color) {
    color = color || getSelected();
    var hex = color.hex.toLowerCase();
    document.documentElement.style.setProperty('--bs-primary', hex);
    document.documentElement.style.setProperty('--bs-primary-rgb', hexToRgbTriplet(hex));
    document.documentElement.setAttribute('data-primary-color', hex);
    if (window.config && config.colors) {
      config.colors.primary = hex;
    }
    ensureStyleEl().textContent = buildOverrides(hex);
  }

  function syncUi(activeId) {
    document.querySelectorAll('.app-primary-color-option').forEach(function (el) {
      var active = el.getAttribute('data-app-primary-color') === activeId;
      el.classList.toggle('active', active);
      var input = el.querySelector('input[type="radio"]');
      if (input) input.checked = active;
    });
  }

  function liveCustomizer() {
    // TemplateCustomizer appends its own panel to body; prefer that one.
    var panels = document.querySelectorAll('body > #template-customizer');
    if (panels.length) return panels[panels.length - 1];
    return document.querySelector('#template-customizer');
  }

  function ensureSection(panel) {
    if (!panel) return null;
    var theming = panel.querySelector('.template-customizer-theming');
    if (!theming) return null;

    var existing = theming.querySelector('.template-customizer-primary-colors');
    if (existing) return existing;

    var styleBlock = theming.querySelector('.template-customizer-style');
    var section = document.createElement('div');
    section.className = 'm-0 px-4 pb-3 pt-1 template-customizer-primary-colors w-100';
    section.innerHTML =
      '<label class="form-label d-block template-customizer-t-primary_colors_label"></label>' +
      '<div class="row g-2 template-customizer-primary-colors-options"></div>';

    if (styleBlock && styleBlock.nextSibling) {
      theming.insertBefore(section, styleBlock.nextSibling);
    } else {
      theming.appendChild(section);
    }
    return section;
  }

  function renderOptions(container) {
    if (!container) return;
    container.innerHTML = '';
    var selected = getSelected();
    var sectionLabel = window.__appPrimaryColorsLabel || 'Primary Colors';

    var labelEl = container
      .closest('.template-customizer-primary-colors')
      .querySelector('.template-customizer-t-primary_colors_label');
    if (labelEl) labelEl.textContent = sectionLabel;

    COLORS.forEach(function (color) {
      var col = document.createElement('div');
      col.className = 'col-6';

      var option = document.createElement('label');
      option.className =
        'app-primary-color-option' + (color.id === selected.id ? ' active' : '');
      option.setAttribute('data-app-primary-color', color.id);

      option.innerHTML =
        '<input class="d-none" type="radio" name="appPrimaryColorRadios" value="' +
        color.id +
        '"' +
        (color.id === selected.id ? ' checked' : '') +
        '>' +
        '<span class="app-primary-color-option-swatch" style="background-color:' +
        color.hex +
        '"></span>' +
        '<span class="app-primary-color-option-meta">' +
        '<span class="app-primary-color-option-name">' +
        colorLabel(color) +
        '</span>' +
        '<span class="app-primary-color-option-hex">' +
        color.hex.toUpperCase() +
        '</span>' +
        '</span>' +
        '<span class="app-primary-color-option-check" aria-hidden="true">' +
        '<svg viewBox="0 0 16 16" width="12" height="12"><path fill="currentColor" d="M6.3 11.5 2.8 8l1.1-1.1 2.4 2.4 5-5L12.4 5.4z"/></svg>' +
        '</span>';

      option.addEventListener('click', function (e) {
        e.preventDefault();
        setSelected(color.id);
      });

      col.appendChild(option);
      container.appendChild(col);
    });
  }

  function mount() {
    var panel = liveCustomizer();
    var section = ensureSection(panel);
    if (!section) return false;
    renderOptions(section.querySelector('.template-customizer-primary-colors-options'));
    syncUi(getSelected().id);
    mounted = true;
    return true;
  }

  function init() {
    apply(getSelected());

    if (mount()) return;

    var tries = 0;
    var timer = setInterval(function () {
      tries += 1;
      if (mount() || tries > 20) clearInterval(timer);
    }, 100);
  }

  return {
    COLORS: COLORS,
    getSelected: getSelected,
    setSelected: setSelected,
    apply: apply,
    init: init,
    mount: mount,
    mountSwatches: mount
  };
})();

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    window.AppPrimaryColor.init();
  });
} else {
  window.AppPrimaryColor.init();
}
