/* Theme cycling: system -> light -> dark, persisted in localStorage.
   Mirrors cycleTheme() from the design. Applied to .app-root[data-theme]. */
(function () {
  var KEY = 'rvc-theme';
  var order = ['system', 'light', 'dark'];
  var glyphs = { system: 'AUTO', light: 'สว่าง', dark: 'มืด' };

  function pref() { return localStorage.getItem(KEY) || 'system'; }
  function sysDark() { return window.matchMedia('(prefers-color-scheme: dark)').matches; }
  function resolved(mode) { return mode === 'system' ? (sysDark() ? 'dark' : 'light') : mode; }

  function apply() {
    var mode = pref();
    var root = document.querySelector('.app-root');
    if (root) root.setAttribute('data-theme', resolved(mode));
    document.querySelectorAll('[data-theme-glyph]').forEach(function (el) {
      el.textContent = glyphs[mode];
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
