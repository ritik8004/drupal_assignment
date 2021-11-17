const Tabby = {
  isTabbyEnabled: () => typeof drupalSettings.tabby !== 'undefined'
      && typeof drupalSettings.tabby.widgetInfo !== 'undefined',

  isAvailable: () => window.Tabby,
};

export default Tabby;
