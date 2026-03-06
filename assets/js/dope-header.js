(function () {
  function parseJSON(raw, fallback) {
    if (!raw) {
      return fallback;
    }

    try {
      var parsed = JSON.parse(raw);
      return parsed && typeof parsed === 'object' ? parsed : fallback;
    } catch (e) {
      return fallback;
    }
  }

  function runCleanups(root) {
    if (!root || !Array.isArray(root._dhCleanups)) {
      return;
    }

    root._dhCleanups.forEach(function (fn) {
      if (typeof fn === 'function') {
        fn();
      }
    });

    root._dhCleanups = [];
  }

  function initTopbar(root, cleanups) {
    var topbar = root.querySelector('.dh-topbar');
    if (!topbar) {
      return;
    }

    var config = parseJSON(topbar.getAttribute('data-dh-config'), {});
    var items = Array.prototype.slice.call(topbar.querySelectorAll('.dh-topbar__item'));
    var prev = topbar.querySelector('[data-dh-prev]');
    var next = topbar.querySelector('[data-dh-next]');

    if (!items.length) {
      return;
    }

    var index = 0;
    var timer = null;

    function show(nextIndex) {
      index = (nextIndex + items.length) % items.length;

      items.forEach(function (item, i) {
        var active = i === index;
        item.hidden = !active;
        item.classList.toggle('is-active', active);
      });
    }

    function go(step) {
      show(index + step);
    }

    function stopAutoplay() {
      if (timer) {
        window.clearInterval(timer);
        timer = null;
      }
    }

    function startAutoplay() {
      stopAutoplay();
      if (!config.autoplay || items.length < 2) {
        return;
      }

      var delay = Number(config.delay);
      if (!Number.isFinite(delay) || delay < 1000) {
        delay = 3500;
      }

      timer = window.setInterval(function () {
        go(1);
      }, delay);
    }

    if (prev) {
      var onPrev = function () {
        go(-1);
      };
      prev.addEventListener('click', onPrev);
      cleanups.push(function () {
        prev.removeEventListener('click', onPrev);
      });
    }

    if (next) {
      var onNext = function () {
        go(1);
      };
      next.addEventListener('click', onNext);
      cleanups.push(function () {
        next.removeEventListener('click', onNext);
      });
    }

    var onKeydown = function (event) {
      if (event.key === 'ArrowLeft') {
        go(-1);
      } else if (event.key === 'ArrowRight') {
        go(1);
      }
    };

    topbar.addEventListener('keydown', onKeydown);
    cleanups.push(function () {
      topbar.removeEventListener('keydown', onKeydown);
    });

    if (config.pauseOnHover) {
      var onEnter = function () {
        stopAutoplay();
      };
      var onLeave = function () {
        startAutoplay();
      };

      topbar.addEventListener('mouseenter', onEnter);
      topbar.addEventListener('mouseleave', onLeave);

      cleanups.push(function () {
        topbar.removeEventListener('mouseenter', onEnter);
        topbar.removeEventListener('mouseleave', onLeave);
      });
    }

    show(index);
    startAutoplay();

    cleanups.push(function () {
      stopAutoplay();
    });
  }

  function initMobileDrawer(root, cleanups) {
    var config = parseJSON(root.getAttribute('data-dh-mobile-config'), {});
    if (!config.enabled) {
      return;
    }

    var toggle = root.querySelector('.dh-mobile-toggle');
    var drawer = root.querySelector('.dh-mobile-drawer');
    if (!toggle || !drawer) {
      return;
    }

    var closeButtons = Array.prototype.slice.call(drawer.querySelectorAll('[data-dh-drawer-close]'));
    var navLinks = Array.prototype.slice.call(drawer.querySelectorAll('.dh-menu--mobile a'));
    var previousOverflow = '';
    var breakpoint = Number(config.breakpoint);

    if (!Number.isFinite(breakpoint)) {
      breakpoint = 1024;
    }

    function syncMobileState() {
      root.classList.toggle('is-mobile', window.innerWidth <= breakpoint);
    }

    function openDrawer() {
      if (drawer.classList.contains('is-open')) {
        return;
      }

      previousOverflow = document.body.style.overflow || '';
      document.body.style.overflow = 'hidden';
      drawer.hidden = false;
      drawer.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
    }

    function closeDrawer() {
      if (!drawer.classList.contains('is-open')) {
        return;
      }

      drawer.classList.remove('is-open');
      drawer.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = previousOverflow;
    }

    var onToggle = function () {
      if (drawer.classList.contains('is-open')) {
        closeDrawer();
      } else {
        openDrawer();
      }
    };

    toggle.addEventListener('click', onToggle);

    closeButtons.forEach(function (button) {
      button.addEventListener('click', closeDrawer);
    });

    var onWindowKeydown = function (event) {
      if (event.key === 'Escape') {
        closeDrawer();
      }
    };

    var onWindowResize = function () {
      if (window.innerWidth > breakpoint) {
        closeDrawer();
      }

      syncMobileState();
    };

    window.addEventListener('keydown', onWindowKeydown);
    window.addEventListener('resize', onWindowResize);
    syncMobileState();

    if (config.closeOnLinkClick) {
      navLinks.forEach(function (link) {
        link.addEventListener('click', closeDrawer);
      });
    }

    cleanups.push(function () {
      toggle.removeEventListener('click', onToggle);
      closeButtons.forEach(function (button) {
        button.removeEventListener('click', closeDrawer);
      });
      navLinks.forEach(function (link) {
        link.removeEventListener('click', closeDrawer);
      });
      window.removeEventListener('keydown', onWindowKeydown);
      window.removeEventListener('resize', onWindowResize);
      root.classList.remove('is-mobile');
      closeDrawer();
    });
  }

  function initWidget(root, force) {
    if (!root) {
      return;
    }

    if (force) {
      runCleanups(root);
    }

    if (root.dataset.dhReady === '1') {
      return;
    }

    var cleanups = [];
    initTopbar(root, cleanups);
    initMobileDrawer(root, cleanups);

    root._dhCleanups = cleanups;
    root.dataset.dhReady = '1';
  }

  function initScope(scope, force) {
    if (scope && scope.classList && scope.classList.contains('dh-widget')) {
      initWidget(scope, force);
      return;
    }

    var context = scope || document;
    if (!context.querySelectorAll) {
      return;
    }

    context.querySelectorAll('.dh-widget').forEach(function (root) {
      initWidget(root, force);
    });
  }

  function getScopeElement(scope) {
    if (!scope) {
      return null;
    }

    if (scope.nodeType === 1) {
      return scope;
    }

    if (scope[0] && scope[0].nodeType === 1) {
      return scope[0];
    }

    if (scope.jquery && typeof scope.get === 'function') {
      return scope.get(0) || null;
    }

    return null;
  }

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/dope_header.default', function ($scope) {
      var scopeElement = getScopeElement($scope);
      if (!scopeElement) {
        return;
      }

      initScope(scopeElement, true);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    initScope(document, false);
  });
})();
