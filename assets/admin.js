/* Admin-side progressive enhancements:
   - collapsible sidebar (persisted)
   - submit form: repeatable authors, keyword chips, chapter upload preview
   - auto-dismiss toasts
   All server actions still work without JS. */
(function () {
  /* ---- sidebar collapse ---- */
  var SB = 'rvc-sidebar';
  function applySidebar() {
    var aside = document.querySelector('[data-sidebar]');
    if (!aside) return;
    var collapsed = localStorage.getItem(SB) === '1';
    aside.setAttribute('data-collapsed', collapsed ? '1' : '0');
    aside.style.width = collapsed ? '68px' : '240px';
    document.querySelectorAll('[data-collapse-hide]').forEach(function (el) {
      el.classList.toggle('sc-hidden', collapsed);
    });
  }
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-action="toggle-sidebar"]');
    if (!t) return;
    localStorage.setItem(SB, localStorage.getItem(SB) === '1' ? '0' : '1');
    applySidebar();
  });

  /* ---- avatar dropdown menu ---- */
  document.addEventListener('click', function (e) {
    var menu = document.querySelector('[data-user-menu]');
    if (!menu) return;
    if (e.target.closest('[data-action="toggle-user-menu"]')) {
      menu.classList.toggle('sc-hidden');
      e.stopPropagation();
      return;
    }
    if (!e.target.closest('[data-user-menu]')) {
      menu.classList.add('sc-hidden');
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var menu = document.querySelector('[data-user-menu]');
      if (menu) menu.classList.add('sc-hidden');
    }
  });

  /* ---- keyword chips ---- */
  function initKeywords() {
    var box = document.querySelector('[data-keywords]');
    if (!box) return;
    var input = box.querySelector('[data-kw-input]');
    var hidden = box.querySelector('[data-kw-hidden]');
    var list = JSON.parse(box.getAttribute('data-keywords') || '[]');

    function render() {
      box.querySelectorAll('[data-chip]').forEach(function (c) { c.remove(); });
      hidden.innerHTML = '';
      list.forEach(function (kw, i) {
        var chip = document.createElement('span');
        chip.setAttribute('data-chip', '');
        chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;font-size:12.5px;background:var(--primary-soft);color:var(--primary-text);padding:4px 6px 4px 11px;border-radius:7px';
        chip.textContent = kw;
        var x = document.createElement('button');
        x.type = 'button';
        x.textContent = '✕';
        x.style.cssText = 'background:none;border:none;color:var(--primary-text);cursor:pointer;font-size:13px;line-height:1;padding:0 2px';
        x.addEventListener('click', function () { list.splice(i, 1); render(); });
        chip.appendChild(x);
        box.insertBefore(chip, input);
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'keywords[]'; inp.value = kw;
        hidden.appendChild(inp);
      });
    }
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var v = input.value.trim();
        if (v) { list.push(v); input.value = ''; render(); }
      }
    });
    render();
  }

  /* ---- repeatable authors ---- */
  function initAuthors() {
    var wrap = document.querySelector('[data-authors]');
    if (!wrap) return;
    var addBtn = document.querySelector('[data-add-author]');
    var tmpl = wrap.querySelector('[data-author-row]');

    function rowHtml() {
      var row = tmpl.cloneNode(true);
      row.querySelector('[data-author-name]').value = '';
      return row;
    }
    if (addBtn) addBtn.addEventListener('click', function () { wrap.appendChild(rowHtml()); });
    wrap.addEventListener('click', function (e) {
      if (e.target.closest('[data-remove-author]')) {
        var rows = wrap.querySelectorAll('[data-author-row]');
        if (rows.length > 1) e.target.closest('[data-author-row]').remove();
      }
    });
  }

  /* ---- chapter upload preview ---- */
  function initChapters() {
    document.querySelectorAll('[data-chapter-file]').forEach(function (input) {
      input.addEventListener('change', function () {
        var row = input.closest('[data-chapter-row]');
        var name = input.files && input.files[0] ? input.files[0].name : '';
        var hint = row.querySelector('[data-chapter-hint]');
        var badge = row.querySelector('[data-chapter-badge]');
        if (name) {
          hint.textContent = name;
          badge.textContent = '✓';
          badge.style.background = 'var(--ok)';
          badge.style.color = '#fff';
          row.style.borderColor = 'var(--ok)';
          row.style.background = 'var(--ok-soft)';
        }
      });
    });
  }

  /* ---- toast auto-dismiss ---- */
  function initToasts() {
    document.querySelectorAll('[data-toast]').forEach(function (t) {
      setTimeout(function () { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; }, 2600);
      setTimeout(function () { t.remove(); }, 3000);
    });
  }

  window.addEventListener('DOMContentLoaded', function () {
    applySidebar();
    initKeywords();
    initAuthors();
    initChapters();
    initToasts();
  });
})();
