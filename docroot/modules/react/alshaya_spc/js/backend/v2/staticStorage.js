window.spcStaticStorage = window.spcStaticStorage || {};

const StaticStorage = {};

StaticStorage.get = (key) => {
  if (typeof window.spcStaticStorage[key] === 'undefined') {
    return null;
  }

  return window.spcStaticStorage[key];
};

StaticStorage.set = (key, value) => {
  window.spcStaticStorage[key] = value;
};

StaticStorage.remove = (key) => {
  window.spcStaticStorage[key] = null;
};

StaticStorage.clear = () => {
  window.spcStaticStorage = {};
};

StaticStorage.delete = (key) => {
  delete window.spcStaticStorage[key];
};

export default StaticStorage;
