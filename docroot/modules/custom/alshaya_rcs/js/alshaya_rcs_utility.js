// Link between RCS errors and Datadog.
(function main() {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });
})();

