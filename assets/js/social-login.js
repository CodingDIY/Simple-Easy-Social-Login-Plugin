document.addEventListener('DOMContentLoaded', function () {
  var errorElement = document.querySelector('.seslp-logins .seslp-inline-error.is-error')
  if (errorElement) {
    setTimeout(function () {
      errorElement.classList.add('is-dismissed')
    }, 5000)
  }

  // Clean up Facebook's trailing fragment `#_=_` after OAuth redirect
  if (window.location && window.location.hash === '#_=_') {
    // If History API is available, clean the URL without adding a new history entry
    if (window.history && typeof window.history.replaceState === 'function') {
      // Replace current URL with the same one minus the fragment (e.g., drop '#_=_')
      window.history.replaceState(null, document.title, window.location.href.split('#')[0])
    } else {
      // Fallback: just drop the hash (will add a history entry on very old browsers)
      window.location.hash = ''
    }
  }
})
