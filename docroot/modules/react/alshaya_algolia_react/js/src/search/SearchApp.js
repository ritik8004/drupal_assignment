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
    };
    // Call createSearchResultDiv() in constructor so that when we
    // check for searchResultsDiv in render(),
    // it'll be available and we'll be able to render SearchResults.
    createSearchResultDiv();
  }

  async componentDidMount() {
    const { query } = this.state;
    if (query !== '') {
      redirectToOtherLang(query);
    }

    // Listing pages product teaser have only express delivery label.
    // This label on teaser is switched on and off by configuration on drupal at
    // global level. However here we call a Magento API to control the display
    // of the label as per magento configuration. We set global variable to avoid
    // this API call again and used on filters and instant search.
    window.sddEdStatus = await getExpressDeliveryStatus();
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
    this.setQueryValue(newValue, inputTag);
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

    return (
      <div>
        <InstantSearch indexName={`${indexName}_query`} searchClient={algoliaSearchClient}>
          <Configure hitsPerPage={drupalSettings.autocomplete.hits} />
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
              {optionalFilter ? <Configure optionalFilters={optionalFilter} /> : null}
              <Configure hitsPerPage={drupalSettings.autocomplete.hits} query={query} />
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
