export const i18nMiddleWareUrl = (url) => {
  const langcode = window.drupalSettings.path.currentLanguage;
  return `${window.drupalSettings.alshaya_spc.middleware_url}/${url}?lang=${langcode}`;
};
