Drupal.alshayaSpc = Drupal.alshayaSpc || {};
Drupal.alshayaSpc.staticStorage = Drupal.alshayaSpc.staticStorage || {};

(function (Drupal) {
  // Object to store the info in static cache.
  var staticStorage = {};

  Drupal.alshayaSpc.staticStorage.get = (key) => {
    if (typeof staticStorage[key] === 'undefined') {
      return null;
    }

    return staticStorage[key];
  };

  Drupal.alshayaSpc.staticStorage.set = (key, value) => {
    staticStorage[key] = value;
  };

  Drupal.alshayaSpc.staticStorage.remove = (key) => {
    staticStorage[key] = null;
  };

  Drupal.alshayaSpc.staticStorage.clear = () => {
    staticStorage = {};
  };
})(Drupal);
