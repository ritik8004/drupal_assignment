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

      // Get the status from supported list of status of DataDog.
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

})(Drupal);
