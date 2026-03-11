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
    var ANIMATION_DURATION = 280;
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
    var submenuToggles = Array.prototype.slice.call(drawer.querySelectorAll('[data-dh-submenu-toggle]'));
    var previousOverflow = '';
    var breakpoint = Number(config.breakpoint);
    var mode = config.mode === 'dropdown' ? 'dropdown' : 'drawer';
    var headerRow = toggle.closest('.dh-main__inner, .dh-minimal-main__inner');
    var closeTimer = null;

    if (!Number.isFinite(breakpoint)) {
      breakpoint = 1024;
    }

    function syncMobileState() {
      root.classList.toggle('is-mobile', window.innerWidth <= breakpoint);
    }

    function updateDropdownPosition() {
      var rowRect;
      var top;

      if (mode !== 'dropdown') {
        return;
      }

      rowRect = headerRow ? headerRow.getBoundingClientRect() : toggle.getBoundingClientRect();
      top = Math.max(rowRect.bottom, 0);

      drawer.style.setProperty('--dh-mobile-menu-top', top + 'px');
    }

    function clearCloseTimer() {
      if (!closeTimer) {
        return;
      }

      window.clearTimeout(closeTimer);
      closeTimer = null;
    }

    function closeAllSubmenus() {
      submenuToggles.forEach(function (button) {
        var controlsId = button.getAttribute('aria-controls');
        var item = button.closest('li');
        var submenu = controlsId ? drawer.querySelector('#' + controlsId) : null;

        button.setAttribute('aria-expanded', 'false');

        if (item) {
          item.classList.remove('is-open');
        }

        if (submenu) {
          submenu.hidden = true;
        }
      });
    }

    function openDrawer() {
      if (drawer.classList.contains('is-open')) {
        return;
      }

      clearCloseTimer();
      updateDropdownPosition();
      previousOverflow = document.body.style.overflow || '';
      document.body.style.overflow = 'hidden';
      drawer.hidden = false;
      window.requestAnimationFrame(function () {
        drawer.classList.add('is-open');
      });
      toggle.setAttribute('aria-expanded', 'true');
      root.classList.add('is-mobile-menu-open');
    }

    function closeDrawer() {
      if (!drawer.classList.contains('is-open')) {
        return;
      }

      closeAllSubmenus();
      drawer.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      root.classList.remove('is-mobile-menu-open');
      clearCloseTimer();
      closeTimer = window.setTimeout(function () {
        drawer.hidden = true;
        document.body.style.overflow = previousOverflow;
        closeTimer = null;
      }, ANIMATION_DURATION);
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

    if (config.submenusEnabled) {
      submenuToggles.forEach(function (button) {
        var onSubmenuToggle = function (event) {
          var controlsId;
          var submenu;
          var item;
          var willOpen;

          event.preventDefault();
          event.stopPropagation();

          controlsId = button.getAttribute('aria-controls');
          submenu = controlsId ? drawer.querySelector('#' + controlsId) : null;
          item = button.closest('li');
          willOpen = button.getAttribute('aria-expanded') !== 'true';

          if (!submenu) {
            return;
          }

          button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
          submenu.hidden = !willOpen;

          if (item) {
            item.classList.toggle('is-open', willOpen);
          }
        };

        button.addEventListener('click', onSubmenuToggle);

        cleanups.push(function () {
          button.removeEventListener('click', onSubmenuToggle);
        });
      });
    }

    var onWindowKeydown = function (event) {
      if (event.key === 'Escape') {
        closeDrawer();
      }
    };

    var onWindowResize = function () {
      if (window.innerWidth > breakpoint) {
        closeDrawer();
      } else if (drawer.classList.contains('is-open')) {
        updateDropdownPosition();
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
      root.classList.remove('is-mobile-menu-open');
      closeAllSubmenus();
      clearCloseTimer();
      drawer.classList.remove('is-open');
      drawer.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = previousOverflow;
    });
  }

  function initCartDrawer(root, cleanups) {
    var ANIMATION_DURATION = 280;
    var drawer = root.querySelector('.dh-cart-drawer');
    var toggles = Array.prototype.slice.call(root.querySelectorAll('[data-dh-cart-toggle]'));

    if (!drawer || !toggles.length) {
      return;
    }

    var closeButtons = Array.prototype.slice.call(drawer.querySelectorAll('[data-dh-cart-close]'));
    var closeTimer = null;

    function clearCloseTimer() {
      if (!closeTimer) {
        return;
      }

      window.clearTimeout(closeTimer);
      closeTimer = null;
    }

    function syncToggles(isOpen) {
      toggles.forEach(function (toggle) {
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
    }

    function openDrawer() {
      if (drawer.classList.contains('is-open')) {
        return;
      }

      clearCloseTimer();
      drawer.hidden = false;
      window.requestAnimationFrame(function () {
        drawer.classList.add('is-open');
      });
      syncToggles(true);
      root.classList.add('is-cart-open');
    }

    function closeDrawer() {
      if (!drawer.classList.contains('is-open')) {
        return;
      }

      drawer.classList.remove('is-open');
      syncToggles(false);
      root.classList.remove('is-cart-open');
      clearCloseTimer();
      closeTimer = window.setTimeout(function () {
        drawer.hidden = true;
        closeTimer = null;
      }, ANIMATION_DURATION);
    }

    var onToggle = function (event) {
      event.preventDefault();

      if (drawer.classList.contains('is-open')) {
        closeDrawer();
      } else {
        openDrawer();
      }
    };

    var onWindowKeydown = function (event) {
      if (event.key === 'Escape') {
        closeDrawer();
      }
    };

    toggles.forEach(function (toggle) {
      toggle.addEventListener('click', onToggle);
    });

    closeButtons.forEach(function (button) {
      button.addEventListener('click', closeDrawer);
    });

    window.addEventListener('keydown', onWindowKeydown);

    cleanups.push(function () {
      toggles.forEach(function (toggle) {
        toggle.removeEventListener('click', onToggle);
      });
      closeButtons.forEach(function (button) {
        button.removeEventListener('click', closeDrawer);
      });
      window.removeEventListener('keydown', onWindowKeydown);
      clearCloseTimer();
      drawer.classList.remove('is-open');
      drawer.hidden = true;
      root.classList.remove('is-cart-open');
      syncToggles(false);
    });
  }

  function initStickyHeader(root, cleanups) {
    if (!root.classList.contains('dh-widget--layout-minimal')) {
      return;
    }

    var config = parseJSON(root.getAttribute('data-dh-sticky-config'), {});
    if (!config.enabled) {
      root.classList.remove('is-scrolled');
      return;
    }

    var shrinkEnabled = !!config.shrink;
    var threshold = Number(config.threshold);
    var rootTop = 0;
    var ticking = false;
    var passiveListener = { passive: true };

    if (!Number.isFinite(threshold) || threshold < 0) {
      threshold = 24;
    }

    function measureRootTop() {
      rootTop = root.getBoundingClientRect().top + window.scrollY;
    }

    function syncStickyState() {
      var scrollTop = window.scrollY || window.pageYOffset || 0;
      var isStuck = scrollTop >= rootTop;

      root.classList.toggle('is-stuck', isStuck);

      if (!shrinkEnabled) {
        root.classList.remove('is-scrolled');
        return;
      }

      root.classList.toggle('is-scrolled', isStuck && scrollTop >= rootTop + threshold);
    }

    function onScroll() {
      if (ticking) {
        return;
      }

      ticking = true;
      window.requestAnimationFrame(function () {
        ticking = false;
        syncStickyState();
      });
    }

    function onResize() {
      measureRootTop();
      syncStickyState();
    }

    measureRootTop();
    syncStickyState();
    window.addEventListener('scroll', onScroll, passiveListener);
    window.addEventListener('resize', onResize);

    cleanups.push(function () {
      window.removeEventListener('scroll', onScroll, passiveListener);
      window.removeEventListener('resize', onResize);
      root.classList.remove('is-stuck');
      root.classList.remove('is-scrolled');
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
    initCartDrawer(root, cleanups);
    initStickyHeader(root, cleanups);

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

  function initEditorSectionDefaults() {
    if (!window.elementor || !window.elementor.hooks) {
      return false;
    }

    var targetSectionIds = [
      'section_topbar',
      'section_header',
      'section_actions',
      'section_mobile',
      'section_style_topbar',
      'section_style_nav',
      'section_style_menu',
      'section_style_actions',
      'section_style_minimal',
      'section_style_mobile'
    ];
    var collapseTimerIds = [];
    var hasNavigationListener = false;

    function clearCollapseTimers() {
      collapseTimerIds.forEach(function (timerId) {
        window.clearTimeout(timerId);
      });

      collapseTimerIds = [];
    }

    function getEditorPanelRoot() {
      return document.querySelector('#elementor-panel-page-editor.elementor-controls-stack');
    }

    function setPanelControlsCollapsing(isCollapsing) {
      var panelRoot = getEditorPanelRoot();
      var controlsRoot = panelRoot ? panelRoot.querySelector('#elementor-controls') : null;
      if (!controlsRoot) {
        return;
      }

      controlsRoot.style.visibility = isCollapsing ? 'hidden' : '';
      controlsRoot.style.pointerEvents = isCollapsing ? 'none' : '';
    }

    function getDopeHeaderPanelRoot() {
      var panelRoot = getEditorPanelRoot();
      if (!panelRoot) {
        return null;
      }

      var hasTargetSection = targetSectionIds.some(function (sectionId) {
        return !!panelRoot.querySelector('.elementor-control-' + sectionId);
      });

      return hasTargetSection ? panelRoot : null;
    }

    function collapseOpenTargetSections() {
      var panelRoot = getDopeHeaderPanelRoot();
      if (!panelRoot) {
        return;
      }

      targetSectionIds.forEach(function (sectionId) {
        var openSection = panelRoot.querySelector('.elementor-control-' + sectionId + '.e-open');
        if (!openSection) {
          return;
        }

        var heading = openSection.querySelector('.elementor-panel-heading');
        if (heading) {
          heading.click();
        }
      });
    }

    function scheduleCollapsePasses() {
      clearCollapseTimers();
      setPanelControlsCollapsing(true);

      [0, 40, 120].forEach(function (delay) {
        collapseTimerIds.push(
          window.setTimeout(function () {
            collapseOpenTargetSections();
          }, delay)
        );
      });

      collapseTimerIds.push(
        window.setTimeout(function () {
          setPanelControlsCollapsing(false);
        }, 170)
      );
    }

    window.elementor.hooks.addAction('panel/open_editor/widget/dope_header', function () {
      setPanelControlsCollapsing(true);
      scheduleCollapsePasses();
    });

    if (!hasNavigationListener) {
      document.addEventListener('click', function (event) {
        var navigationTab = event.target.closest('.elementor-panel-navigation-tab');
        if (!navigationTab || !getDopeHeaderPanelRoot()) {
          return;
        }

        setPanelControlsCollapsing(true);

        window.setTimeout(function () {
          if (getDopeHeaderPanelRoot()) {
            scheduleCollapsePasses();
          } else {
            setPanelControlsCollapsing(false);
          }
        }, 0);
      });

      hasNavigationListener = true;
    }

    return true;
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

  if (!initEditorSectionDefaults()) {
    document.addEventListener('DOMContentLoaded', function () {
      initEditorSectionDefaults();
    });
  }
})();
