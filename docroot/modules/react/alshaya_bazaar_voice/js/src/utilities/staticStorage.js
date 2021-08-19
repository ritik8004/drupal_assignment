window.bazaarVoiceStaticStorage = window.bazaarVoiceStaticStorage || {};

const StaticStorage = {};

StaticStorage.get = (key) => {
  if (typeof window.bazaarVoiceStaticStorage[key] === 'undefined') {
    return null;
  }

  return window.bazaarVoiceStaticStorage[key];
};

StaticStorage.set = (key, value) => {
  window.bazaarVoiceStaticStorage[key] = value;
};

StaticStorage.remove = (key) => {
  window.bazaarVoiceStaticStorage[key] = null;
};

StaticStorage.clear = () => {
  window.bazaarVoiceStaticStorage = {};
};

export default StaticStorage;
