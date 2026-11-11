/**
 * App primary color picker
 * Persists selection and remaps theme primary shades across the whole app.
 */
'use strict';

window.AppPrimaryColor = (function () {
  var BASE_PRIMARY = '#696cff';

  // Purple-family shades used by default / semi-dark / bordered themes
  var BASE_SHADES = [
    '#2a2b4b', '#35365f', '#38396d', '#3e3f79', '#434d99', '#4f51bf', '#595cd9',
    '#5f61e6', '#6467f2', '#696cff', '#6a6cc9', '#6d70ff', '#7174ff', '#787bff',
    '#8385ff', '#878899', '#b0b2ff', '#c2c4ff', '#c3c4ff', '#c8c8ff', '#cbcbe6',
    '#cfd0eb', '#d1d4f1', '#d2d3ff', '#d6d7f2', '#d8daf6', '#dcdefb', '#e0e1ff',
    '#e1e2ff', '#e7e7ff', '#e9e9ff'
  ];

  // Raspberry-family shades (so remapping still works if that theme CSS is active)
  var RASPBERRY_PRIMARY = '#e30b5c';
  var RASPBERRY_SHADES = [
    '#341c31', '#482744', '#581f41', '#622248', '#792250', '#957c85', '#a14080',
    '#aa0845', '#c1094e', '#cc0a53', '#d80a57', '#e0b9c8', '#e30b5c', '#e41160',
    '#e51764', '#e5becc', '#e6236c', '#e7c2d3', '#e83578', '#edc4d3', '#eec7d7',
    '#f076a3', '#f391b5', '#f49dbe', '#f4cadb', '#f7b6ce', '#f8bcd2', '#f8bed3',
    '#f9cede', '#fbd8e5', '#fbdae7'
  ];

  var COLORS = [
    { id: 'default', hex: '#696cff' },
    { id: 'raspberry', hex: '#e30b5c' },
    { id: 'ocean', hex: '#00cfe8' },
    { id: 'forest', hex: '#28c76f' },
    { id: 'amber', hex: '#ff9f43' },
    { id: 'blue', hex: '#2092ec' },
    { id: 'violet', hex: '#9c27b0' },
    { id: 'slate', hex: '#4b465c' }
  ];

  function storageKey() {
    var template = document.documentElement.getAttribute('data-template') || 'vertical-menu-template';
    return 'templateCustomizer-' + template + '--PrimaryColor';
  }

  function hexToRgb(hex) {
    hex = String(hex).replace('#', '');
    if (hex.length === 3) {
      hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
    }
    return [
      parseInt(hex.slice(0, 2), 16) / 255,
      parseInt(hex.slice(2, 4), 16) / 255,
      parseInt(hex.slice(4, 6), 16) / 255
    ];
  }

  function rgbToHex(rgb) {
    return (
      '#' +
      rgb
        .map(function (c) {
          var v = Math.round(Math.min(1, Math.max(0, c)) * 255);
          return (v < 16 ? '0' : '') + v.toString(16);
        })
        .join('')
    );
  }

  function rgbToCssTriplet(hex) {
    var rgb = hexToRgb(hex);
    return [
      Math.round(rgb[0] * 255),
      Math.round(rgb[1] * 255),
      Math.round(rgb[2] * 255)
    ].join(', ');
  }

  function mix(a, b, t) {
    return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t, a[2] + (b[2] - a[2]) * t];
  }

  function approxMix(target, base) {
    var best = { err: 1e9, toward: 'white', t: 0 };
    var targets = [
      ['white', [1, 1, 1]],
      ['black', [0, 0, 0]]
    ];
    for (var i = 0; i < targets.length; i++) {
      var toward = targets[i][0];
      var towardRgb = targets[i][1];
      var ts = [];
      for (var c = 0; c < 3; c++) {
        var denom = towardRgb[c] - base[c];
        if (Math.abs(denom) < 1e-6) continue;
        ts.push((target[c] - base[c]) / denom);
      }
      if (!ts.length) continue;
      var t =
        ts.reduce(function (s, v) {
          return s + v;
        }, 0) / ts.length;
      t = Math.min(1, Math.max(0, t));
      var got = mix(base, towardRgb, t);
      var err = Math.sqrt(
        Math.pow(got[0] - target[0], 2) + Math.pow(got[1] - target[1], 2) + Math.pow(got[2] - target[2], 2)
      );
      if (err < best.err) {
        best = { err: err, toward: toward, t: t };
      }
    }
    return best;
  }

  function recolorShade(oldHex, oldPrimaryHex, newPrimaryHex) {
    var oldRgb = hexToRgb(oldHex);
    var oldPrimary = hexToRgb(oldPrimaryHex);
    var newPrimary = hexToRgb(newPrimaryHex);
    if (oldHex.toLowerCase() === oldPrimaryHex.toLowerCase()) {
      return newPrimaryHex.toLowerCase();
    }
    var fit = approxMix(oldRgb, oldPrimary);
    var towardRgb = fit.toward === 'white' ? [1, 1, 1] : [0, 0, 0];
    return rgbToHex(mix(newPrimary, towardRgb, fit.t));
  }

  function buildReplacementMap(newPrimary) {
    var map = {};
    var i;
    for (i = 0; i < BASE_SHADES.length; i++) {
      map[BASE_SHADES[i]] = recolorShade(BASE_SHADES[i], BASE_PRIMARY, newPrimary);
    }
    for (i = 0; i < RASPBERRY_SHADES.length; i++) {
      map[RASPBERRY_SHADES[i]] = recolorShade(RASPBERRY_SHADES[i], RASPBERRY_PRIMARY, newPrimary);
    }
    map[BASE_PRIMARY] = newPrimary.toLowerCase();
    map[RASPBERRY_PRIMARY] = newPrimary.toLowerCase();
    return map;
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

  function setSelected(colorIdOrHex) {
    var color = null;
    for (var i = 0; i < COLORS.length; i++) {
      if (COLORS[i].id === colorIdOrHex || COLORS[i].hex.toLowerCase() === String(colorIdOrHex).toLowerCase()) {
        color = COLORS[i];
        break;
      }
    }
    if (!color) return;
    try {
      localStorage.setItem(storageKey(), color.id);
    } catch (e) {}
    apply(color.hex);
    syncSwatches(color.id);
  }

  function setCssVars(hex) {
    var root = document.documentElement;
    root.style.setProperty('--bs-primary', hex);
    root.style.setProperty('--bs-primary-rgb', rgbToCssTriplet(hex));
    root.setAttribute('data-primary-color', hex.toLowerCase());
    if (window.config && config.colors) {
      config.colors.primary = hex;
    }
  }

  function replaceCssColors(cssText, map) {
    return cssText.replace(/#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})\b/g, function (match) {
      var hex = match.toLowerCase();
      if (hex.length === 4) {
        hex = '#' + hex[1] + hex[1] + hex[2] + hex[2] + hex[3] + hex[3];
      }
      return map[hex] || match;
    });
  }

  function ensureOverrideStyle() {
    var style = document.getElementById('app-primary-color-overrides');
    if (!style) {
      style = document.createElement('style');
      style.id = 'app-primary-color-overrides';
      document.head.appendChild(style);
    }
    return style;
  }

  function utilityOverrides(hex) {
    var soft = recolorShade('#e7e7ff', BASE_PRIMARY, hex);
    var soft2 = recolorShade('#e0e1ff', BASE_PRIMARY, hex);
    var dark = recolorShade('#5f61e6', BASE_PRIMARY, hex);
    return [
      ':root{--bs-primary:' + hex + ';--bs-primary-rgb:' + rgbToCssTriplet(hex) + ';}',
      '.bg-primary,.btn-primary,.btn-primary:hover,.btn-primary:focus,.btn-primary:active,.btn-primary.active,',
      '.dropdown-item.active,.dropdown-item:active,.page-item.active .page-link,',
      '.progress-bar,.form-check-input:checked,.nav-pills .nav-link.active,',
      '.list-group-item.active,.badge.bg-primary{background-color:' + hex + ' !important;border-color:' + hex + ' !important;}',
      '.text-primary,.btn-outline-primary,.btn-label-primary,.page-link,',
      'a.text-primary:hover,.menu-vertical .menu-item.active > .menu-link:not(.menu-toggle){color:' + hex + ' !important;}',
      '.btn-outline-primary{border-color:' + hex + ' !important;color:' + hex + ' !important;}',
      '.btn-outline-primary:hover,.btn-outline-primary:focus,.btn-outline-primary:active{background-color:' + hex + ' !important;color:#fff !important;}',
      '.border-primary{border-color:' + hex + ' !important;}',
      '.bg-label-primary{background-color:' + soft + ' !important;color:' + hex + ' !important;}',
      '.alert-primary{background-color:' + soft2 + ' !important;border-color:' + soft + ' !important;color:' + dark + ' !important;}',
      '.form-control:focus,.form-select:focus{border-color:' + hex + ' !important;}',
      '.layout-wrapper:not(.layout-horizontal) .bg-menu-theme .menu-inner > .menu-item.active:before{background:' + hex + ' !important;}',
      '.template-customizer-open-btn{background-color:' + hex + ' !important;}'
    ].join('');
  }

  function recolorStylesheets(newPrimary) {
    var map = buildReplacementMap(newPrimary);
    var style = ensureOverrideStyle();
    var chunks = [utilityOverrides(newPrimary)];
    var links = document.querySelectorAll('link.template-customizer-theme-css');
    var pending = [];
    var seen = {};

    links.forEach(function (link) {
      if (!link.href || seen[link.href]) return;
      seen[link.href] = true;
      pending.push(
        fetch(link.href, { credentials: 'same-origin' })
          .then(function (res) {
            return res.ok ? res.text() : '';
          })
          .then(function (css) {
            if (css) chunks.push(replaceCssColors(css, map));
          })
          .catch(function () {})
      );
    });

    return Promise.all(pending).then(function () {
      style.textContent = chunks.join('\n');
    });
  }

  function apply(hex) {
    hex = (hex || getSelected().hex).toLowerCase();
    setCssVars(hex);
    return recolorStylesheets(hex);
  }

  function syncSwatches(activeId) {
    var nodes = document.querySelectorAll('[data-app-primary-color]');
    nodes.forEach(function (el) {
      var id = el.getAttribute('data-app-primary-color');
      el.classList.toggle('active', id === activeId);
      el.setAttribute('aria-pressed', id === activeId ? 'true' : 'false');
    });
  }

  function ensureColorsSection() {
    var themingBlocks = document.querySelectorAll('.template-customizer-theming');
    themingBlocks.forEach(function (theming) {
      if (theming.querySelector('.template-customizer-primary-colors')) return;

      var section = document.createElement('div');
      section.className = 'm-0 px-4 pb-3 pt-1 template-customizer-primary-colors w-100';
      section.innerHTML =
        '<label class="form-label d-block template-customizer-t-primary_colors_label"></label>' +
        '<div class="d-flex flex-wrap gap-2 align-items-center template-customizer-primary-colors-options"></div>';
      theming.appendChild(section);
    });

    var labelNodes = document.querySelectorAll('.template-customizer-t-primary_colors_label');
    var labelText =
      (window.__appPrimaryColorsLabel) ||
      (document.documentElement.lang === 'en' ? 'Primary Colors' : 'رنگ‌های اصلی');
    labelNodes.forEach(function (node) {
      if (!node.textContent) node.textContent = labelText;
    });
  }

  function renderSwatches(container) {
    if (!container) return;
    container.innerHTML = '';
    var selected = getSelected();
    COLORS.forEach(function (color) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'app-primary-color-swatch' + (color.id === selected.id ? ' active' : '');
      btn.setAttribute('data-app-primary-color', color.id);
      btn.setAttribute('title', color.id);
      btn.setAttribute('aria-label', color.id);
      btn.setAttribute('aria-pressed', color.id === selected.id ? 'true' : 'false');
      btn.style.backgroundColor = color.hex;
      btn.style.color = color.hex;
      btn.addEventListener('click', function () {
        setSelected(color.id);
      });
      container.appendChild(btn);
    });
  }

  function mountSwatches() {
    ensureColorsSection();
    document.querySelectorAll('.template-customizer-primary-colors-options').forEach(function (container) {
      renderSwatches(container);
    });
    syncSwatches(getSelected().id);
  }

  function init() {
    var selected = getSelected();
    setCssVars(selected.hex);
    apply(selected.hex).finally(function () {
      mountSwatches();
      // TemplateCustomizer appends its panel asynchronously; remount shortly after.
      setTimeout(mountSwatches, 50);
      setTimeout(mountSwatches, 300);
    });
  }

  return {
    COLORS: COLORS,
    getSelected: getSelected,
    setSelected: setSelected,
    apply: apply,
    init: init,
    mountSwatches: mountSwatches,
    renderSwatches: renderSwatches
  };
})();

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    window.AppPrimaryColor.init();
  });
} else {
  window.AppPrimaryColor.init();
}
