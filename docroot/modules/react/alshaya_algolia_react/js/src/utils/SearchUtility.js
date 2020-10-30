import { hasCategoryFilter } from './FilterUtils';
import { getSearchQuery, getLangRedirect } from './localStorage';

const contentDiv = document.querySelector('.page-standard main');
const body = document.querySelector('body');

const pageStandard = document.querySelector('.page-standard');
const defaultClasses = pageStandard.className;
let searchClasses = 'page-standard c-plp c-plp-only ';
searchClasses += hasCategoryFilter() ? 'l-two--sf l-container' : 'l-one--w lhn-without-sidebar l-container';

// Create Search result div wrapper to render results.
function createSearchResultDiv() {
  const searchResultDiv = document.createElement('div');
  searchResultDiv.id = 'alshaya-algolia-search';
  searchResultDiv.style.display = 'none';
  contentDiv.parentNode.insertBefore(searchResultDiv, contentDiv.nextSibling);
}

function showSearchResultContainer() {
  Array.prototype.forEach.call(contentDiv.parentNode.children, (element) => {
    const searchContainerElm = element;
    searchContainerElm.style.display = 'none';
  });
  const searchQuery = getSearchQuery();
  const searchResultDiv = document.getElementById('alshaya-algolia-search');

  // On search page, we always show search results. So need to hide header on VS
  // only when there is search query.
  if (searchQuery !== '' && searchQuery !== null) {
    body.classList.add('hide-header');
  } else {
    body.classList.remove('hide-header');
  }
  searchResultDiv.style.display = 'block';
  searchResultDiv.className = 'show-algolia-result';
  searchResultDiv.style.minHeight = '26.5rem';
  pageStandard.className = searchClasses;
}

function hideSearchResultContainer() {
  if (typeof Drupal.blazy !== 'undefined') {
    Drupal.blazy.revalidate();
  }
  Array.prototype.forEach.call(contentDiv.parentNode.children, (element) => {
    const searchContainerElm = element;
    searchContainerElm.style.display = null;
  });
  const searchResultDiv = document.getElementById('alshaya-algolia-search');
  body.classList.remove('hide-header');
  searchResultDiv.style.display = 'none';
  searchResultDiv.classList.remove('show-algolia-result');
  pageStandard.className = defaultClasses;
  Drupal.blazyRevalidate();
}

function toggleSearchResultsContainer() {
  // When user is on search page, we always want to display search results,
  // As search links are used internally with filters
  const searchQuery = getSearchQuery();
  if (drupalSettings.algoliaSearch.showSearchResults) {
    showSearchResultContainer();
  } else if (searchQuery === '' || searchQuery === null) {
    hideSearchResultContainer();
  } else {
    showSearchResultContainer();
  }
}

// Show or hide sort by filter, when no results found.
function toggleSortByFilter(action, context = 'alshaya-algolia-search') {
  const searchWrapper = document.getElementById(context);

  if (action === 'hide') {
    searchWrapper.querySelector('.container-without-product #sort_by').classList.add('hide-facet-block');
  } else {
    searchWrapper.querySelector('.container-without-product #sort_by').classList.remove('hide-facet-block');
  }
}

/**
 * Place ajax fulll screen loader.
 */
function showLoader() {
  let loaderDiv = document.getElementsByClassName('ajax-progress-fullscreen');
  if (loaderDiv.length > 0) {
    return;
  }
  loaderDiv = document.createElement('div');
  loaderDiv.className = 'ajax-progress ajax-progress-fullscreen';
  document.body.appendChild(loaderDiv);
}

/**
 * Remove ajax loader.
 */
function removeLoader() {
  const loaderDiv = document.getElementsByClassName('ajax-progress-fullscreen');
  // Check if loader div is present algolia is not redirecting to other language.
  if (loaderDiv.length > 0 && getLangRedirect() !== '1') {
    document.body.removeChild(loaderDiv[0]);
  }
}

export {
  contentDiv,
  createSearchResultDiv,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  showLoader,
  removeLoader,
};
