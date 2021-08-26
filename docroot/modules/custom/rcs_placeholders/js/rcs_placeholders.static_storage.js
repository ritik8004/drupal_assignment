window.rcsPhStaticStorage = window.rcsPhStaticStorage || {};

const RcsPhStaticStorage = {};

RcsPhStaticStorage.get = (key) => {
  if (typeof window.rcsPhStaticStorage[key] === 'undefined') {
    return null;
  }

  return window.rcsPhStaticStorage[key];
};

RcsPhStaticStorage.getAll = () => {
  return window.rcsPhStaticStorage;
};

RcsPhStaticStorage.set = (key, value) => {
  window.rcsPhStaticStorage[key] = value;
};

RcsPhStaticStorage.remove = (key) => {
  window.rcsPhStaticStorage[key] = null;
};

RcsPhStaticStorage.clear = () => {
  window.rcsPhStaticStorage = {};
};
