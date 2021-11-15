const Tabby = {
  isTabbyEnabled: () => {
    if (typeof drupalSettings.tabby !== 'undefined'
      && typeof drupalSettings.tabby.widgetInfo !== 'undefined') {
      return true;
    }
    return false;
  },
};

export default Tabby;
