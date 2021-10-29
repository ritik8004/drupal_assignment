const Tabby = {
  isTabbyEnabled: () => {
    if (typeof drupalSettings.tabby_widget_info !== 'undefined'
      && typeof drupalSettings.tabby !== 'undefined') {
      return true;
    }
    return false;
  },
};

export default Tabby;
