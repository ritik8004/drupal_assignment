import qs from 'qs';
import { createBrowserHistory } from 'history';
import { toggleSearchResultsContainer, showLoader } from './SearchUtility';
import { set_lang_redirect } from './localStorage';

const updateAfter = 700;
const history = createBrowserHistory();

/**
 * Get current raw search query string from brwoser hash and convert it to object.
 */
function getCurrentSearchQueryString() {
  return qs.parse(window.location.hash.substr(1));
}

/**
 * Get search query from current browser hash.
 */
function getCurrentSearchQuery() {
  const parsedHash = getCurrentSearchQueryString();
  return parsedHash && parsedHash.query ? parsedHash.query : '';
}

/**
 * Push query to browser histroy to go back and see previous results.
 *
 * @param {*} queryValue
 *   The search string to push to browser hash.
 */
function updateSearchQuery(queryString) {
  history.push({hash: queryString});
}

/**
 * Check if search state contains a filter or not.
 *
 * @param {*} searchState
 *   current search state.
 */
function searchStateHasFilter(searchState) {
  if (Object.keys(searchState).length > 0 && Object.keys(searchState).includes('refinementList')) {
    return Object.values(searchState.refinementList).filter(v => v.length !== 0).length > 0;
  }
  return false;
}

/**
 * Try to redirect to other langauge if required.
 *
 * @param {*} queryValue
 *   The current text of query value.
 * @param {*} inputTag
 *   The search input element.
 */
function redirectToOtherLang(queryValue, inputTag) {
  if (queryValue.length === 0) {
    toggleSearchResultsContainer();
    return;
  }

  let arabicText = /[\u0600-\u06FF\u0750-\u077F]/.test(queryValue);
  const redirectlang = arabicText ? 'ar' : 'en';
  redirectToUrl(queryValue, redirectlang, inputTag);
}

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
function redirectToUrl(queryValue, redirectlang, inputTag) {
  if (drupalSettings.path.currentLanguage !== redirectlang) {
    showLoader();
    // Disable input tag while redirecting to other language.
    if (inputTag !== null && typeof inputTag !== 'undefined') {
      inputTag.disabled = true;
    }
    set_lang_redirect(1);
    window.location.hash = "query=" + queryValue;
    window.location.pathname = window.location.pathname.replace(drupalSettings.path.currentLanguage, redirectlang);
  }
}

/**
 * Return true if current view is mobile otherwise false.
 */
function isMobile() {
  return (window.innerWidth < 768);
}

/**
 * Get the storage values.
 *
 * @returns {null}
 */
function getAlgoliaStorageValues() {
  var value = localStorage.getItem(window.location.hash);
  if (typeof value !== 'undefined' && value !== null) {
    return JSON.parse(value);
  }

  return null;
}

export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues,
  searchStateHasFilter
}
