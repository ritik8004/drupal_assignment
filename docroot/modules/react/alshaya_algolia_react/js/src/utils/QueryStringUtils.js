import qs from 'qs';
import { createBrowserHistory } from 'history';
import { toggleSearchResultsContainer, showLoader } from './SearchUtility';
import { setLangRedirect } from './localStorage';

const updateAfter = 700;

/**
 * Get current raw search query string from brwoser hash and convert it to object.
 */
const getCurrentSearchQueryString = () => (qs.parse(window.location.hash.substr(1)));

/**
 * Get search query from current browser hash.
 */
const getCurrentSearchQuery = () => {
  const parsedHash = getCurrentSearchQueryString();
  return parsedHash && parsedHash.query ? parsedHash.query : '';
};

/**
 * Push query to browser histroy to go back and see previous results.
 *
 * @param {*} queryValue
 *   The search string to push to browser hash.
 */
const updateSearchQuery = (queryString) => {
  const currentHistory = createBrowserHistory();
  if (currentHistory.location.hash !== queryString) {
    currentHistory.push({ hash: queryString }, { action: 'search', queryString });
  }
};

/**
 * Check if search state contains a filter or not.
 *
 * @param {*} searchState
 *   current search state.
 */
const searchStateHasFilter = (searchState) => {
  if (Object.keys(searchState).length > 0 && Object.keys(searchState).includes('refinementList')) {
    return Object.values(searchState.refinementList).filter((v) => v.length !== 0).length > 0;
  }
  return false;
};

/**
 * Redirect to given redirectlang with query value.
 *
 * @param {*} queryValue
 *   The current text of query value.
 * @param {*} redirectlang
 *   The language string to replace with current one and redirect to.
 * @param {*} inputTag
 *   The search input element.
 */
const redirectToUrl = (queryValue, redirectlang, inputTag) => {
  if (drupalSettings.path.currentLanguage !== redirectlang) {
    showLoader();
    const disableInputTag = inputTag;
    // Disable input tag while redirecting to other language.
    if (disableInputTag !== null && typeof disableInputTag !== 'undefined') {
      disableInputTag.disabled = true;
    }
    setLangRedirect(1);
    window.location.hash = `query=${queryValue}`;
    window.location.pathname = window.location.pathname.replace(
      drupalSettings.path.currentLanguage,
      redirectlang,
    );
  }
};

/**
 * Try to redirect to other langauge if required.
 *
 * @param {*} queryValue
 *   The current text of query value.
 * @param {*} inputTag
 *   The search input element.
 */
const redirectToOtherLang = (queryValue, inputTag) => {
  if (queryValue.length === 0) {
    toggleSearchResultsContainer();
    return;
  }

  const arabicText = /[\u0600-\u06FF\u0750-\u077F]/.test(queryValue);
  const redirectlang = arabicText ? 'ar' : 'en';
  redirectToUrl(queryValue, redirectlang, inputTag);
};

/**
 * Return true if current view is mobile otherwise false.
 *
 * @deprecated: Please use it from global utilities.
 */
const isMobile = () => (window.innerWidth < 768);

/**
 * Return true if current view is desktop otherwise false.
 *
 * @deprecated: Please use it from global utilities.
 */
const isDesktop = () => (window.innerWidth > 1024);

/**
 * Get the storage values.
 *
 * @returns {null}
 */
const getAlgoliaStorageValues = () => Drupal.getItemFromLocalStorage(`search:${window.location.hash}`);

export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  isDesktop,
  getAlgoliaStorageValues,
  searchStateHasFilter,
};
