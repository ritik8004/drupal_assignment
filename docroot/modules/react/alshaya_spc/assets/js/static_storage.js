window.staticStorage = window.spcStaticStorage || {};
window.spcStaticStorage = window.spcStaticStorage || {}

window.staticStorage.get = (key) => {
  if (typeof window.spcStaticStorage[key] === 'undefined') {
    return null;
  }

  return window.spcStaticStorage[key];
};

window.staticStorage.set = (key, value) => {
  window.spcStaticStorage[key] = value;
};

window.staticStorage.remove = (key) => {
  window.spcStaticStorage[key] = null;
};

window.staticStorage.clear = () => {
  window.spcStaticStorage = {};
};
