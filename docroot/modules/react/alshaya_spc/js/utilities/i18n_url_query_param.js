const i18nMiddleWareUrlWithQueryParam = (url) => {
  const langcode = window.drupalSettings.path.currentLanguage;
  const splitUrl = url.split('?');
  const queryParam = splitUrl[1]
    ? `${splitUrl[1]}&lang=${langcode}`
    : `lang=${langcode}`;

  return `${window.drupalSettings.alshaya_spc.middleware_url}/${splitUrl[0]}?${queryParam}`;
};

export default i18nMiddleWareUrlWithQueryParam;
