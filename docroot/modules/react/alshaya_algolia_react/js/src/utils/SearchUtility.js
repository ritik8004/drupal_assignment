import { hasCategoryFilter } from './FilterUtils';

var contentDiv = document.querySelector('.page-standard main');
// Create Search result div wrapper to render results.
var searchResultDiv = document.createElement('div');
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
}

function toggleSearchResultsContainer(query) {
  (typeof query === 'undefined' || query == '')
    ? hideSearchResultContainer()
    : showSearchResultContainer();
}

export {contentDiv, searchResultDiv, toggleSearchResultsContainer};
