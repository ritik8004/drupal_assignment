const i18nMiddleWareUrlWithQuery = (url) => {
  const langcode = window.drupalSettings.path.currentLanguage;
  const urlParts = url.split('?');
  const queryParameter = (urlParts[1] === undefined)
    ? `lang=${langcode}`
    : `${urlParts[1]}&lang=${langcode}`;

  return `${window.drupalSettings.alshaya_spc.middleware_url}/${urlParts[0]}?${queryParameter}`;
};

export default i18nMiddleWareUrlWithQuery;
