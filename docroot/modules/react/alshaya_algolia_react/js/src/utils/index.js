export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues,
  searchStateHasFilter
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

export {
  setSearchQuery,
  removeSearchQuery,
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem
} from './localStorage';
