export {
  searchStateToURL,
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues
} from './QueryStringUtils';

export {
  getPriceRangeLabel,
  calculateDiscount,
  formatPrice
} from './PriceHelper';

export {
  contentDiv,
  searchResultDiv,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  showLoader,
  removeLoader
} from './SearchUtility';

export {
  getFilters,
  hasCategoryFilter
} from './FilterUtils';
