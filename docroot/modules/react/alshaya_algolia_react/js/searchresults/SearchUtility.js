// var contentDiv = document.querySelector('.page-standard main');
var contentDiv = document.querySelector('#block-content');
// Create Search result div wrapper to render results.
var searchResultDiv = document.createElement('div');

searchResultDiv.id = 'alshaya-algolia-search';
// searchResultDiv.className = 'c-plp-only l-one--w lhn-without-sidebar';
searchResultDiv.style.display = 'none';
contentDiv.parentNode.insertBefore( searchResultDiv, contentDiv.nextSibling );

var pageStandard = document.querySelector('.page-standard');
var defaultClasses = pageStandard.className;
var searchClasses = "page-standard c-plp c-plp-only l-one--w lhn-without-sidebar l-container";

function showSearchResultContainer() {
  Array.prototype.forEach.call(contentDiv.parentNode.children, element => {
    element.style.display = 'none';
  });
  searchResultDiv.style.display = 'block';
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
  pageStandard.className = defaultClasses;
}

function toggleSearchResultsContainer(query) {
  (typeof query === 'uundefined' || query == '')
    ? hideSearchResultContainer()
    : showSearchResultContainer();
}


export {contentDiv, searchResultDiv, toggleSearchResultsContainer};
