document.addEventListener('DOMContentLoaded', function () {
  var debugEnabledRadios = document.querySelectorAll('input[name="seslp_options[debug][enabled]"]')
  var tzRow = document.getElementById('seslp-tz-row')
  function toggleTz() {
    var val = document.querySelector('input[name="seslp_options[debug][enabled]"]:checked').value
    tzRow.style.display = val === '1' ? '' : 'none'
  }
  debugEnabledRadios.forEach(r => r.addEventListener('change', toggleTz))
  toggleTz()
})

// Uninstall options: toggle Deep Clean row when Data Removal is checked
;(function () {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSeslpUninstallToggles)
  } else {
    initSeslpUninstallToggles()
  }
  function initSeslpUninstallToggles() {
    var removeData = document.querySelector('input[data-seslp-role="remove-data"]')
    var deepRow = document.getElementById('seslp-deep-row')
    if (!removeData || !deepRow) return // not on this screen
    function sync() {
      var on = !!removeData.checked
      deepRow.style.display = on ? '' : 'none'
      if (!on) {
        var deep = document.querySelector('input[data-seslp-role="deep-clean"]')
        if (deep) deep.checked = false
      }
    }
    removeData.addEventListener('change', sync)
    sync()
  }
})()
