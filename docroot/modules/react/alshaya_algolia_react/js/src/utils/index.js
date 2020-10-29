export {
  getCurrentSearchQueryString,
  getCurrentSearchQuery,
  updateSearchQuery,
  updateAfter,
  redirectToOtherLang,
  isMobile,
  getAlgoliaStorageValues,
  searchStateHasFilter,
} from './QueryStringUtils';

export {
  getPriceRangeLabel,
  calculateDiscount,
  formatPrice,
} from './PriceHelper';

export {
  contentDiv,
  createSearchResultDiv,
  toggleSearchResultsContainer,
  toggleSortByFilter,
  showLoader,
  removeLoader,
} from './SearchUtility';

export {
  getFilters,
  hasCategoryFilter,
  getSortedItems,
  hasSuperCategoryFilter,
  facetFieldAlias,
  customQueryRedirect,
} from './FilterUtils';

export {
  setSearchQuery,
  removeSearchQuery,
  getSearchQuery,
  setLangRedirect,
  removeLangRedirect,
  getLangRedirect,
  setClickedItem,
  storeClickedItem,
} from './localStorage';

export {
  getSuperCategory,
  getSuperCategoryOptionalFilter,
} from './SuperCategoryUtility';
