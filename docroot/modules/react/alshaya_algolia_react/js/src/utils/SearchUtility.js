import { hasCategoryFilter } from './FilterUtils';

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
    element.style.display = 'block';
  });
  searchResultDiv.style.display = 'none';
  searchResultDiv.classList.remove('show-algolia-result');
  pageStandard.className = defaultClasses;
  Drupal.blazyRevalidate();
}

function toggleSearchResultsContainer(query) {
  (typeof query === 'undefined' || query === '')
    ? hideSearchResultContainer()
    : showSearchResultContainer();
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
  if (loaderDiv.length > 0) {
    document.body.removeChild(loaderDiv[0]);
  }
}

export {
  contentDiv,
  searchResultDiv,
  toggleSearchResultsContainer,
  showLoader,
  removeLoader
};
