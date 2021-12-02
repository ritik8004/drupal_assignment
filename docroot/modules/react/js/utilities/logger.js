/**
 * Logs messages in the backend.
 *
 * @param {string} level
 *   The error level.
 * @param {string} message
 *   The message.
 * @param {string} context
 *   The context.
 */
const logger = {
  send: (level, message, context) => {
    if (typeof Drupal.logViaDataDog !== 'undefined') {
      Drupal.logViaDataDog(level, message, context);
      return;
    }

    // Avoid console log in npm tests.
    if (typeof drupalSettings.jest !== 'undefined') {
      return;
    }

    // eslint-disable-next-line no-console
    console.debug(level, Drupal.formatString(message, context));
  },
  emergency: (message, context) => logger.send('emergency', message, context),
  alert: (message, context) => logger.send('alert', message, context),
  critical: (message, context) => logger.send('critical', message, context),
  error: (message, context) => logger.send('error', message, context),
  warning: (message, context) => logger.send('warning', message, context),
  notice: (message, context) => logger.send('notice', message, context),
  info: (message, context) => logger.send('info', message, context),
  debug: (message, context) => logger.send('debug', message, context),
};

export default logger;
