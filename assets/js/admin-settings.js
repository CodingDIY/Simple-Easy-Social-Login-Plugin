;(function () {
  'use strict'

  // Run init functions when DOM is ready (and only once)
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn, { once: true })
    } else {
      fn()
    }
  }

  /**
   * Debug settings: toggle timezone row when debug is enabled
   */
  function initDebugTimezoneToggle() {
    var debugRadios = document.querySelectorAll('input[name="seslp_options[debug][enabled]"]')
    var tzRow = document.getElementById('seslp-tz-row')

    // Not on Debug Settings screen
    if (!debugRadios.length || !tzRow) return

    function toggleTz() {
      var checked = document.querySelector('input[name="seslp_options[debug][enabled]"]:checked')
      var val = checked ? checked.value : '0'
      tzRow.style.display = val === '1' ? '' : 'none'
    }

    debugRadios.forEach(function (radio) {
      radio.addEventListener('change', toggleTz)
    })

    toggleTz()
  }

  /**
   * Uninstall options: toggle Deep Clean row when Data Removal is checked
   */
  function initUninstallToggles() {
    var removeData = document.querySelector('input[data-seslp-role="remove-data"]')
    var deepRow = document.getElementById('seslp-deep-row')

    // Not on Uninstall screen
    if (!removeData || !deepRow) return

    var deepCheckbox = document.querySelector('input[data-seslp-role="deep-clean"]')

    function sync() {
      var on = !!removeData.checked
      deepRow.style.display = on ? '' : 'none'

      // When turning off, also uncheck deep-clean
      if (!on && deepCheckbox) {
        deepCheckbox.checked = false
      }
    }

    removeData.addEventListener('change', sync)
    sync()
  }

  /**
   * Guide page: allow only one <details> open at a time
   */
  function initGuideAccordion() {
    var container = document.querySelector('.seslp-guide-content')
    if (!container) return

    var allDetails = container.querySelectorAll('details')
    if (!allDetails.length) return

    container.addEventListener(
      'toggle',
      function (event) {
        var target = event.target
        if (!target || target.tagName.toLowerCase() !== 'details') return
        if (!target.open) return // only act when a <details> becomes open

        allDetails.forEach(function (d) {
          if (d !== target) {
            d.open = false
          }
        })
      },
      true, // capture phase to catch all toggle events
    )
  }

  // Register all initializers
  onReady(initDebugTimezoneToggle)
  onReady(initUninstallToggles)
  onReady(initGuideAccordion)
})()
