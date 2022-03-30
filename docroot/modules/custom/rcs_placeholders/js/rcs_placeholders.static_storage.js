globalThis.RcsPhStaticStorage = {};
const rcsPhStaticStorage = {};

globalThis.RcsPhStaticStorage.get = (key) => {
  if (typeof rcsPhStaticStorage[key] === 'undefined') {
    return null;
  }

  return rcsPhStaticStorage[key];
};

globalThis.RcsPhStaticStorage.getAll = () => {
  return rcsPhStaticStorage;
};

globalThis.RcsPhStaticStorage.set = (key, value) => {
  rcsPhStaticStorage[key] = value;
};

globalThis.RcsPhStaticStorage.remove = (key) => {
  rcsPhStaticStorage[key] = null;
};

globalThis.RcsPhStaticStorage.clear = () => {
  rcsPhStaticStorage = {};
};
