// Link between RCS errors and Datadog.
(function main(Drupal, RcsEventManager) {
  RcsEventManager.addListener('error', (e) => {
    Drupal.alshayaLogger(e.level, e.message, e.context);
  });
})(Drupal, RcsEventManager);
