(function (Drupal) {
  // Initial a variable with page load timestamp to identify the actual time.
  // This time will remain unchanged within the context of window.
  window.pageLoadTime = window.pageLoadTime || new Date().getTime();
  let pageUuid = '';

  Drupal.logViaDataDog = function (severity, message, context) {
    try {
      if (typeof window.DD_LOGS === 'undefined') {
        return false;
      }

      // Make sure context is defined.
      context = context || {};

      context.logSource = 'drupal_module';

      // Pass the actual page load time upon user request to Datadog log for
      // better monitoring.
      context.pageLoadTime = window.pageLoadTime;

      // Generate the unique ID only once for a page and pass to Datadog log for better monitoring.
      if (!pageUuid) {
        pageUuid = generateUUID();
      }
      context.pageUuid = pageUuid;

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

})(Drupal);
