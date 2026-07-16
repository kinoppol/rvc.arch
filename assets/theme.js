/* Theme cycling: system -> light -> dark, persisted in localStorage.
   Mirrors cycleTheme() from the design. Applied to .app-root[data-theme]. */
(function () {
  var KEY = 'rvc-theme';
  var order = ['system', 'light', 'dark'];
  var labels = { system: 'ตามระบบ', light: 'สว่าง', dark: 'มืด' };
  var SVG = 'viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
  var icons = {
    // monitor = follow system
    system: '<svg ' + SVG + '><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
    // sun = light
    light: '<svg ' + SVG + '><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="4"/><line x1="12" y1="20" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="4" y2="12"/><line x1="20" y1="12" x2="22" y2="12"/><line x1="4.93" y1="19.07" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.07" y2="4.93"/></svg>',
    // moon = dark
    dark: '<svg ' + SVG + '><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>'
  };

  function pref() { return localStorage.getItem(KEY) || 'system'; }
  function sysDark() { return window.matchMedia('(prefers-color-scheme: dark)').matches; }
  function resolved(mode) { return mode === 'system' ? (sysDark() ? 'dark' : 'light') : mode; }

  function apply() {
    var mode = pref();
    var root = document.querySelector('.app-root');
    if (root) root.setAttribute('data-theme', resolved(mode));
    document.querySelectorAll('[data-theme-glyph]').forEach(function (el) {
      el.innerHTML = icons[mode];
      var btn = el.closest('button');
      if (btn) btn.setAttribute('title', 'โหมดแสดงผล: ' + labels[mode]);
    });
  }

  // Apply as early as possible (this script is in <head>) to avoid a flash.
  document.documentElement.setAttribute('data-pref-theme', resolved(pref()));

  window.addEventListener('DOMContentLoaded', function () {
    apply();
    document.querySelectorAll('[data-action="cycle-theme"]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = order[(order.indexOf(pref()) + 1) % order.length];
        localStorage.setItem(KEY, next);
        apply();
      });
    });
  });

  // Follow OS changes while in "system" mode.
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
    if (pref() === 'system') apply();
  });
})();
