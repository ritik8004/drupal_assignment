import { hasCategoryFilter } from './FilterUtils';
import { getSearchQuery, getLangRedirect } from './localStorage';

const contentDiv = document.querySelector('.page-standard main');
// Create Search result div wrapper to render results.
const searchResultDiv = document.createElement('div');
searchResultDiv.id = 'alshaya-algolia-search';
searchResultDiv.style.display = 'none';
contentDiv.parentNode.insertBefore( searchResultDiv, contentDiv.nextSibling );

var pageStandard = document.querySelector('.page-standard');
var defaultClasses = pageStandard.className;
var searchClasses = "page-standard c-plp c-plp-only ";
searchClasses += hasCategoryFilter() ? "l-two--sf l-container" : "l-one--w lhn-without-sidebar l-container";

function showSearchResultContainer() {
  Array.prototype.forEach.call(contentDiv.parentNode.children, element => {
    element.style.display = 'none';
  });
  searchResultDiv.style.display = 'block';
  searchResultDiv.className = 'show-algolia-result';
  searchResultDiv.style.minHeight = '26.5rem';
  pageStandard.className = searchClasses;
}

function hideSearchResultContainer() {
  if (typeof Drupal.blazy !== 'undefined') {
    Drupal.blazy.revalidate();
  }
  Array.prototype.forEach.call(contentDiv.parentNode.children, element => {
    element.style.display = null;
  });
  searchResultDiv.style.display = 'none';
  searchResultDiv.classList.remove('show-algolia-result');
  pageStandard.className = defaultClasses;
  Drupal.blazyRevalidate();
}

function toggleSearchResultsContainer() {
  // When user is on search page, we always want to display search results,
  // As search links are used internally with filters
  let search_query = getSearchQuery();
  if (drupalSettings.algoliaSearch.showSearchResults) {
    showSearchResultContainer();
  }
  else if (search_query === '' || search_query === null) {
    hideSearchResultContainer();
  }
  else {
    showSearchResultContainer();
  }
}

// Show or hide sort by filter, when no results found.
function toggleSortByFilter(action) {
  const searchWrapper = document.getElementById('alshaya-algolia-search');

  if (action == 'hide') {
    searchWrapper.querySelector('.container-without-product #sort_by').classList.add('hide-facet-block')
  }
  else {
    searchWrapper.querySelector('.container-without-product #sort_by').classList.remove('hide-facet-block')
  }

}

/**
 * Place ajax fulll screen loader.
 */
function showLoader() {
  const loaderDiv = document.createElement( 'div' );
  loaderDiv.className = 'ajax-progress ajax-progress-fullscreen';
  document.body.appendChild( loaderDiv );
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
  searchResultDiv,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  showLoader,
  removeLoader
};
