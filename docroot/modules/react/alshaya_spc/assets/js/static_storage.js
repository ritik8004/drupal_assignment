Drupal.alshayaSpc = Drupal.alshayaSpc || {};
Drupal.alshayaSpc.staticStorage = Drupal.alshayaSpc.staticStorage || {}
// Empty object to store the info.
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
