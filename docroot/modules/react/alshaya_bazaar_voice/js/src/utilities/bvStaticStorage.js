window.bazaarVoiceStaticStorage = window.bazaarVoiceStaticStorage || {};

const BVStaticStorage = {};

BVStaticStorage.get = (key) => {
  if (typeof window.bazaarVoiceStaticStorage[key] === 'undefined') {
    return null;
  }

  return window.bazaarVoiceStaticStorage[key];
};

BVStaticStorage.set = (key, value) => {
  window.bazaarVoiceStaticStorage[key] = value;
};

BVStaticStorage.remove = (key) => {
  window.bazaarVoiceStaticStorage[key] = null;
};

BVStaticStorage.clear = () => {
  window.bazaarVoiceStaticStorage = {};
};

export default BVStaticStorage;
