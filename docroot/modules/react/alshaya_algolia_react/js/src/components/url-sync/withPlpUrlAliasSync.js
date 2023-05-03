import React, { Component } from 'react';
import { createBrowserHistory } from 'history';
import {
  updateAfter,
  facetFieldAlias,
  isMobile,
  showLoader,
} from '../../utils';
import { getFacetStorage, asyncFacetValuesRequest } from '../../utils/requests';
import { productListIndexStatus } from '../../utils/indexUtils';
import { isConfigurableFiltersEnabled } from '../../../../../js/utilities/helper';

const history = createBrowserHistory();

/**
 * Create and return Object containing range filters with granularity.
 */
const multiRangeFilters = () => {
  if (!multiRangeFilters.cache.results) {
    const { filters } = drupalSettings.algoliaSearch.listing;
    const results = [];
    Object.entries(filters).forEach(([key, value]) => {
      if (value.widget.type === 'range_checkbox') {
        const { currentLanguage } = drupalSettings.path;
        const facetKey = productListIndexStatus() ? `${key}.${currentLanguage}` : key;
        results[facetKey] = parseInt(value.widget.config.granularity, 10);
      }
    });
    multiRangeFilters.cache = { results };
  }
  return multiRangeFilters.cache.results;
};

multiRangeFilters.cache = {};

/**
 * Create and return Object containing hierachical filters with
 * key and value.
 */
const hierarchicalFilters = () => {
  if (!hierarchicalFilters.cache.results) {
    const { filters } = drupalSettings.algoliaSearch.listing;
    const results = [];
    Object.entries(filters).forEach(([key, value]) => {
      if (value.widget.type === 'hierarchy') {
        results[key] = value.alias;
      }
    });
    hierarchicalFilters.cache = { results };
  }
  return hierarchicalFilters.cache.results;
};

hierarchicalFilters.cache = {};

/**
 * Create and return Object containing hierachical filters with
 * key and value.
 */
const swatchFiltersList = () => {
  if (!swatchFiltersList.cache.results) {
    const { filters } = drupalSettings.algoliaSearch.listing;
    const results = [];
    Object.entries(filters).forEach(([key, value]) => {
      if (value.widget.type === 'swatch_list') {
        const { currentLanguage } = drupalSettings.path;
        const facetKey = productListIndexStatus() ? `${key}.${currentLanguage}` : key;
        results[facetKey] = value.alias;
      }
    });
    swatchFiltersList.cache = { results };
  }
  return swatchFiltersList.cache.results;
};

swatchFiltersList.cache = {};

/**
 * Helper function to convert partial url containing filter string
 * into an array.
 *
 * @param {*} filterString
 *   The string containing partial url (e.g. --age-18-20/--size-NO%20SIZE)
 *
 * return an array with, key and value of filters:
 * [
 *   {key: "age", value: ["18", 20]},
 *   {key: "size", value: ["NO SIZE"]}
 * ]
 */
const filtersStringToArray = (filterString) => filterString.split('/')
  .filter((str) => str.length > 0)
  .map((filter) => {
    const values = filter.split('-').filter((str) => str.length > 0);
    return {
      key: facetFieldAlias(values.shift(), 'key'),
      values,
    };
  });

/**
 * Prepare an object for basePath, default filters to show,
 * based on current url.
 */
const getBaseRouteAndFilters = (byPassCache = false) => {
  if (!getBaseRouteAndFilters.cache.results || byPassCache) {
    const currentLocation = { ...history.location };
    const pathWithFilters = currentLocation.pathname;
    const filtersIndex = pathWithFilters.indexOf('/--');
    const filters = filtersStringToArray(pathWithFilters.slice(filtersIndex));

    const results = {
      currentLocation,
      basePath: pathWithFilters.slice(0, filtersIndex),
      filters,
    };
    getBaseRouteAndFilters.cache = { results };
  }

  return getBaseRouteAndFilters.cache.results;
};

// Initiate empty cache property for the function to store the already
// processed values.
getBaseRouteAndFilters.cache = {};

const convertAliasToFilterValues = (fieldName, facetAlias, fieldValues, type) => {
  if (type === 'multiRange') {
    const rangeFilters = multiRangeFilters();
    const elementValue = parseInt(fieldValues.slice(0, 1), 10);
    return elementValue === 0
      ? `:${rangeFilters[fieldName]}`
      : `${elementValue}:${elementValue + rangeFilters[fieldName]}`;
  }

  const facetValues = getFacetStorage(facetAlias, true);
  const returnObj = { replacedValues: [], makeApiRequest: false };
  if (type === 'refinementList') {
    if (!facetValues) {
      returnObj.makeApiRequest = true;
      return returnObj;
    }

    fieldValues.forEach((item) => {
      if (!facetValues[item]) {
        returnObj.makeApiRequest = true;
      } else {
        returnObj.replacedValues.push(facetValues[item]);
      }
    });
    return returnObj;
  }

  if (type === 'hierarchicalMenu') {
    if (!facetValues || !facetValues[fieldValues]) {
      returnObj.makeApiRequest = true;
      return returnObj;
    }
    returnObj.replacedValues = facetValues[fieldValues];
    return returnObj;
  }
  return false;
};

const withPlpUrlAliasSync = (
  ChildComponent, pageType, pageSubType,
) => class withPlpUrlAlias extends Component {
  constructor(props) {
    super(props);

    this.pageSubType = pageSubType;
    this.categoryFieldName = 'lhn_category';
    this.state = {
      searchState: {
        multiRange: {},
        refinementList: {},
      },
    };
  }

  async componentDidMount() {
    if (this.pageSubType !== 'plp') {
      return;
    }

    // Update the state based on url for selected filters / sort.
    showLoader();

    if (isConfigurableFiltersEnabled()) {
      // Wait till userData is received then apply filters.
      document.addEventListener('userDataReceived', this.applyFilters);
    } else {
      // apply filters from drupal settings data.
      await this.applyFilters();
    }
  }

  componentWillUnmount() {
    if (this.pageSubType !== 'plp') {
      return;
    }

    clearTimeout(this.debouncedSetState);
    window.removeEventListener('popstate', this.onPopState);
  }

  applyFilters = async () => {
    const { filters } = getBaseRouteAndFilters();
    if (filters.length > 0) {
      const decodedFilters = await this.getRefinementListForFilters(filters);
      this.setState({
        searchState: decodedFilters,
      });
    }

    window.addEventListener('popstate', this.onPopState);
  }

  onPopState = async (event) => {
    // On browser back click if state is null, reset everything.
    const emptySearchState = {
      multiRange: {},
      refinementList: {},
    };

    if (!event.state) {
      this.setState({ searchState: emptySearchState });
      return null;
    }

    // On browser back click if state is not null, but it does not contain
    // state related to plp then it's not plp state change. Return
    // immediately without any action.
    const { state: historyState } = event.state;
    if (!historyState.action || historyState.action !== pageType) {
      this.setState((prevState) => {
        if (JSON.stringify(prevState.searchState) !== JSON.stringify(emptySearchState)) {
          return { searchState: emptySearchState };
        }
        return null;
      });
      return null;
    }

    // Update filters from state to update results of plp page.
    const filters = filtersStringToArray(historyState.filters);
    let searchState = {};
    if (filters.length > 0 && historyState) {
      const decodedFilters = await this.getRefinementListForFilters(filters);
      searchState = { ...decodedFilters };
    }
    this.setState({ searchState });
    return null;
  }

  getRefinementListForFilters = async (filters) => {
    const rangeFilters = multiRangeFilters();
    const hierarchyFilters = hierarchicalFilters();
    const swatchFilters = swatchFiltersList();

    const filterList = {
      multiRange: {},
      refinementList: {},
    };

    const apiRequests = [];
    filters.forEach((element) => {
      const elementKey = element.key.split('.').shift();
      const facetAlias = facetFieldAlias(element.key, 'alias', pageType);

      // Range filters (e.g "price") requires values separated by colon(:)
      // [e.g 0:5, 5:10 etc..], filter value in url contains only min. value
      // that's why we have to prepare a range value using granularity mapping
      // that we did in multiRangeFilters().
      if (rangeFilters[element.key]) {
        filterList.multiRange[element.key] = convertAliasToFilterValues(element.key, facetAlias, element.values, 'multiRange');
        return;
      }

      // Convert all filters alias values to actual value that matches with the one
      // that we have in filters, for that we are using apis which can return the
      // mapping of values and alias.
      // refinementList: {attr_age_group: ['bieber_purpose_tour'], attr_size: ['1_2m'] }
      // into: {attr_age_group: ['Bieber Purpose Tour'], attr_size: ['1-2M'] }
      // Try to get the alias from local storage if exists.
      // const facetValues = getFacetStorage(facetAlias, true);
      if (!filterList.refinementList[elementKey] && !hierarchyFilters[elementKey]) {
        let facetKey = element.key;

        if (typeof swatchFilters[element.key] !== 'undefined') {
          facetKey = `${element.key}.value`;
        }

        filterList.refinementList[facetKey] = element.values;
        const { replacedValues, makeApiRequest } = convertAliasToFilterValues(element.key, facetAlias, element.values, 'refinementList');
        if (!makeApiRequest && replacedValues.length > 0) {
          filterList.refinementList[facetKey] = replacedValues;
        }

        if (makeApiRequest) {
          apiRequests[facetAlias] = facetKey;
        }
        return;
      }

      if (hierarchyFilters[elementKey] && elementKey === 'field_category') {
        if (!filterList.hierarchicalMenu) {
          filterList.hierarchicalMenu = {};
        }

        let categoryFieldFacet = `${this.categoryFieldName}.lvl0`;

        if (productListIndexStatus()) {
          const { currentLanguage } = drupalSettings.path;
          categoryFieldFacet = (this.categoryFieldName === 'lhn_category')
            ? `${this.categoryFieldName}.${currentLanguage}.lvl0`
            : `${this.categoryFieldName}.en.lvl0`;
        }

        const hierarchyValue = element.values.slice(0, 1);
        filterList.hierarchicalMenu[categoryFieldFacet] = hierarchyValue;
        const {
          replacedValues: hreplacedValues,
          makeApiRequest: hmakeApiRequest,
        } = convertAliasToFilterValues(element.key, facetAlias, hierarchyValue, 'hierarchicalMenu');

        if (!hmakeApiRequest && hreplacedValues.length > 0) {
          filterList.hierarchicalMenu[categoryFieldFacet] = hreplacedValues;
        } else if (hmakeApiRequest) {
          apiRequests[facetAlias] = categoryFieldFacet;
        }
      }
    });

    // Make api requests for missing facets item values and update filter keys with values.
    if (Object.keys(apiRequests).length > 0) {
      const facetResults = await asyncFacetValuesRequest(Object.keys(apiRequests));
      Object.entries(facetResults).forEach(([facetKey, values]) => {
        if (Object.keys(values).length === 0) return;
        const facetAlias = apiRequests[facetKey];
        if (Object.keys(values).length > 0 && filterList.refinementList[facetAlias] && facetKey !== 'category') {
          const replacedValues = filterList.refinementList[facetAlias].map(
            (item) => values[item.trim()] || item,
          );
          filterList.refinementList[facetAlias] = replacedValues;
        } else if (facetKey === 'category') {
          const termId = filterList.hierarchicalMenu[facetAlias];
          if (values[termId]) {
            filterList.hierarchicalMenu[facetAlias] = values[termId].trim();
          }
        }
      });
    }
    return filterList;
  }

  onPlpStateChange = async (searchState, stateChange = true) => {
    if (!stateChange) return;
    showLoader();
    const newSearchState = { ...searchState };

    // Configure contains internal settings like oos filter, results per
    // page etc.. we don't want to pass it in query string.
    delete newSearchState.configure;

    // Convert all filters into url, converts following refinement object.
    // refinementList: {attr_age_group: ['test', 'test2'], attr_accessories_style: ["test"] }
    // into: --age-test-test2/--style-test1
    const filters = [];
    if (isMobile() && newSearchState.hierarchicalMenu) {
      const hierachiApi = [];
      Object.entries(newSearchState.hierarchicalMenu).forEach(([facetKey, value]) => {
        if (!value) return;
        const facetKeyAlias = facetFieldAlias(
          facetKey.indexOf(this.categoryFieldName) >= 0 ? 'field_category' : facetKey,
          'alias',
        );
        if (!facetKeyAlias) return;
        const trimedValue = value.trim();

        const {
          hierarchy: defaultCategoryFilter,
        } = drupalSettings.algoliaSearch;

        // Do not add current category in url.
        if (trimedValue === defaultCategoryFilter) {
          return;
        }

        // Convert facet values to aliases to send it to url.
        const facetValues = getFacetStorage(facetKeyAlias);
        if ((!facetValues || facetValues.length === 0 || !facetValues[trimedValue])
        && !hierachiApi[facetKeyAlias]
        ) {
          hierachiApi[facetKeyAlias] = facetKey;
          return;
        }
        filters.push(`--${facetKeyAlias}-${facetValues[trimedValue]}`);
      });

      if (Object.keys(hierachiApi).length > 0) {
        // Make api call to get alias for missing facet item value.
        const hierarchiValues = await asyncFacetValuesRequest(Object.keys(hierachiApi), false);
        Object.entries(hierarchiValues).forEach(([facetKeyAlias, facetValues]) => {
          const facetFieldName = hierachiApi[facetKeyAlias];
          const hierarchiFacetValue = newSearchState.hierarchicalMenu[facetFieldName].trim();
          if (facetValues[hierarchiFacetValue]) {
            filters.push(`--${facetKeyAlias}-${facetValues[hierarchiFacetValue]}`);
          }
        });
      }
    }

    if (newSearchState.refinementList) {
      const freshApiRequests = [];
      Object.entries(newSearchState.refinementList).forEach(([facetKey, value]) => {
        if (!value) return;
        const facetKeyAlias = facetFieldAlias(facetKey, 'alias', pageType);
        // Convert facet values to aliases to send it to url.
        const facetValues = getFacetStorage(facetKeyAlias);
        const aliasValues = [];
        value.forEach(async (origString) => {
          const trimedorigString = origString.trim();
          if (!facetValues || !facetValues[trimedorigString]) {
            freshApiRequests.push(facetKeyAlias);
          } else {
            aliasValues.push(facetValues[trimedorigString]);
          }
        });

        if (freshApiRequests.indexOf(facetKeyAlias) < 0 && aliasValues.length > 0) {
          filters.push(`--${facetKeyAlias}-${aliasValues.join('-')}`);
        }
      });

      // Make api call to get alias for missing facet item value.
      if (freshApiRequests.length > 0) {
        const newFacetValues = await asyncFacetValuesRequest(freshApiRequests, false);
        Object.entries(newFacetValues).forEach(([facetKeyAlias, facetValues]) => {
          const aliasValues = [];
          const facetFieldName = facetFieldAlias(facetKeyAlias, 'key');
          newSearchState.refinementList[facetFieldName].forEach((origValue) => {
            const trimedorigValue = origValue.trim();
            if (facetValues[trimedorigValue]) aliasValues.push(facetValues[trimedorigValue]);
          });
          if (aliasValues.length > 0) {
            filters.push(`--${facetKeyAlias}-${aliasValues.join('-')}`);
          }
        });
      }
    }

    if (newSearchState.multiRange) {
      Object.entries(newSearchState.multiRange).forEach(
        ([key, value]) => {
          // Convert price facet values to aliases to send it to url.
          const filterKey = facetFieldAlias(key, 'alias');
          const minVal = value.split(':').shift();
          if (value.length > 0) filters.push(`--${filterKey}-${minVal.length > 0 ? minVal : 0}`);
        },
      );
    }

    if (newSearchState) {
      this.setState(
        (prevState) => {
          if (JSON.stringify(prevState.searchState) !== JSON.stringify(newSearchState)) {
            return { searchState: newSearchState };
          }
          return null;
        },
        () => this.updateCurrentUrl(filters.join('/')),
      );
    }
  };

  /**
   * Update current url for given filters.
   *
   * @param {*} filters
   */
  updateCurrentUrl = (filters) => {
    if (this.pageSubType !== 'plp') {
      return;
    }

    clearTimeout(this.debouncedSetState);
    const { currentLocation, basePath } = getBaseRouteAndFilters();
    this.debouncedSetState = setTimeout(() => {
      const filtersPath = filters.length > 0 ? `${filters}/` : '';
      // Delete previous state while pushing it to history as we are pushing
      // new state object as second argument of history.push().
      delete currentLocation.state;
      // Delete hash from object, as with hash we won't be able to access plp
      // page, and if this function is triggered that means hash is not needed
      // right now.
      delete currentLocation.hash;
      history.push({
        ...currentLocation, ...{ pathname: `${basePath}/${filtersPath}` },
      }, { action: pageType, filters: filtersPath });
    }, updateAfter);
  }

  render() {
    const { searchState } = this.state;

    return (
      <ChildComponent
        {...this.props}
        searchState={searchState}
        createURL={(newPlpState) => this.onPlpStateChange(newPlpState, false)}
        onSearchStateChange={this.onPlpStateChange}
        pageType={pageType}
      />
    );
  }
};

export default withPlpUrlAliasSync;
