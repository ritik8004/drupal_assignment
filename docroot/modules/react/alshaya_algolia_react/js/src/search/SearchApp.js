import React from 'react';
import {
  InstantSearch,
  Configure,
  Hits,
  QueryRuleCustomData,
} from 'react-instantsearch-dom';
import AutoComplete from '../components/algolia/Autocomplete';
import SearchResults from '../components/searchresults';
import Portal from '../components/portal';
import Teaser from '../components/teaser';
import {
  getCurrentSearchQuery,
  isMobile,
  redirectToOtherLang,
  setSearchQuery,
  getSuperCategoryOptionalFilter,
  customQueryRedirect,
  createSearchResultDiv,
} from '../utils';
import { algoliaSearchClient } from '../config/SearchClient';
import { getExpressDeliveryStatus } from '../../../../js/utilities/expressDeliveryHelper';

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

class SearchApp extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      query: getCurrentSearchQuery(),
      sddEdStatus: null,
    };
    // Call createSearchResultDiv() in constructor so that when we
    // check for searchResultsDiv in render(),
    // it'll be available and we'll be able to render SearchResults.
    createSearchResultDiv();
  }

  componentDidMount() {
    const { query } = this.state;
    if (query !== '') {
      redirectToOtherLang(query);
    }
    // For search page, this value is true on page load. So we make the API
    // call to fetch SDD/ED status and store it globally.
    if (window.algoliaSearchActivityStarted) {
      this.setSddEdStatus();
    }
  }

  setQueryValue = (queryValue, inputTag = null) => {
    this.setState({ query: queryValue });
    setSearchQuery(queryValue);
    if (queryValue !== '') {
      redirectToOtherLang(queryValue, inputTag);
    }
  };

  onSuggestionSelected = (event, { suggestion }) => {
    this.setQueryValue(suggestion.query);
  };

  onSuggestionCleared = () => {
    this.setQueryValue('');
  };

  onChange = (newValue, inputTag) => {
    const { sddEdStatus } = this.state;
    if (sddEdStatus === null) {
      this.setSddEdStatus();
    }

    this.setQueryValue(newValue, inputTag);
  };

  setSddEdStatus = () => {
    const { sddEdStatus } = this.state;
    // Listing pages product teaser have only express delivery label.
    // This label on teaser is switched on and off by configuration on drupal at
    // global level. However here we call a Magento API to control the display
    // of the label as per magento configuration. We set global variable to avoid
    // this API call again and used on filters and instant search.
    if (sddEdStatus === null) {
      getExpressDeliveryStatus().then((status) => {
        window.sddEdStatus = status;
        this.setState({ sddEdStatus: status });
      });
    }
  };

  render() {
    const { query } = this.state;
    // Display search results when wrapper is present on page.
    const searchWrapper = document.getElementById('alshaya-algolia-search');
    const searchResultsDiv = (typeof searchWrapper !== 'undefined' && searchWrapper != null)
      ? (<SearchResults query={query} />)
      : '';
    const optionalFilter = getSuperCategoryOptionalFilter();

    const { indexName } = drupalSettings.algoliaSearch.search;
    // For enabling/disabling hitsPerPage key in algolia calls.
    const enableHitsPerPage = drupalSettings.algoliaSearch.hitsPerPage;

    // For checking state of predictiveSearch.
    const { predictiveSearchEnabled } = drupalSettings.algoliaSearch;

    return (
      <div className={predictiveSearchEnabled ? 'predictive-search' : null}>
        <InstantSearch indexName={`${indexName}_query`} searchClient={algoliaSearchClient}>
          <Configure
            {...(enableHitsPerPage && { hitsPerPage: drupalSettings.autocomplete.hits })}
            userToken={Drupal.getAlgoliaUserToken()}
          />
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            onChange={this.onChange}
          />
          <QueryRuleCustomData transformItems={(items) => customQueryRedirect(items)}>
            {() => null}
          </QueryRuleCustomData>
        </InstantSearch>
        {isMobile() && (
          <Portal id="top-results" conditional query={query}>
            <span className="top-suggestions-title">{Drupal.t('top suggestions')}</span>
            <InstantSearch
              indexName={indexName}
              searchClient={algoliaSearchClient}
            >
              {optionalFilter ? (
                <Configure
                  optionalFilters={optionalFilter}
                  userToken={Drupal.getAlgoliaUserToken()}
                />
              ) : null}
              <Configure
                {...(enableHitsPerPage && { hitsPerPage: drupalSettings.autocomplete.hits })}
                userToken={Drupal.getAlgoliaUserToken()}
                query={query}
              />
              <Hits hitComponent={Teaser} />
            </InstantSearch>
          </Portal>
        )}
        {searchResultsDiv}
      </div>
    );
  }
}

export default SearchApp;
