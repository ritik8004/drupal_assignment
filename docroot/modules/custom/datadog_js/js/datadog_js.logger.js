(function (Drupal) {

  Drupal.logViaDataDog = function (severity, message, context) {
    try {
      if (typeof window.DD_LOGS === 'undefined') {
        return false;
      }

      // Make sure context is defined.
      context = context || {};

      context.logSource = 'drupal_module';

      // Let other modules alter contexts.
      document.dispatchEvent(new CustomEvent('dataDogContextAlter', {
          bubbles: true,
          detail: context,
        })
      );

      // Get the status from supported list of status of Datadog.
      var status = 'debug';

      switch (severity) {
        case 'warning':
        case 'warn':
          status = 'warn';
          break;

        case 'critical':
        case 'emergency':
        case 'error':
          status = 'error';
          break;

        case 'alert':
        case 'notice':
        case 'info':
          status = 'info';
          break;

      }

      window.DD_LOGS.logger.log(message, context, status);

      return true;
    }
    catch (e) {
    }

    return false;
  };

  /**
   * Create a random Guid.
   *
   * @return {String} a random guid value.
   */
  function generateUUID() {
    var d = window.pageLoadTime;
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      var r = (d + Math.random()*16)%16 | 0;
      d = Math.floor(d/16);
      return (c=='x' ? r : (r&0x3|0x8)).toString(16);
    });

    return uuid;
  };

  if (window.DD_LOGS) {
    var globalContext = window.DD_LOGS.getLoggerGlobalContext();

    // Generate the unique ID only once for a page and pass to Datadog log
    // for better monitoring.
    globalContext.pageUuid = generateUUID();

    // Pass the actual page load time upon user request to Datadog log for
    // better monitoring.
    globalContext.pageLoadTime = new Date().getTime();

    // Pass flag weather browser is modern or not.
    var browserType = Drupal.isModernBrowser();
    globalContext.isModernBrowser = !!browserType.isModernBrowser;

    window.DD_LOGS.setLoggerGlobalContext(globalContext);

    if (!browserType.isModernBrowser) {
      var errorData = {
        event: 'eventTracker',
        eventCategory: 'unknown errors',
        eventLabel: 'error-checking-modern-browser',
        eventAction: browserType.isModernBrowserError,
        eventPlace: 'Error occurred on ' + window.location.href,
        eventValue: 0,
        nonInteraction: 0,
      };
      // Log what error caused failure determining modern browser.
      Drupal.logViaDataDog('error', 'Error occurred checking modern browser.', errorData);
    }
  }

})(Drupal);
