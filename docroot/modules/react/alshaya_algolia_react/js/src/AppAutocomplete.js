import React from 'react';
import ReactDOM from 'react-dom';
import { InstantSearch } from 'react-instantsearch-dom';
import { Configure, Hits } from "react-instantsearch-dom";
import {searchClient} from './config/SearchClient';
import AutoComplete from './Autocomplete';
import SearchResultsRender from './searchresults/SearchResultsRender';
import Portal from './components/Portal/Portal';
import Teaser from './components/teaser/Teaser';
import {
  toggleSearchResultsContainer,
  getCurrentSearchQuery,
  isMobile,
  updateSearchQuery
} from './utils';

class AppAutocomplete extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      query: getCurrentSearchQuery()
    };
    toggleSearchResultsContainer(this.state.query);
  };

  setQueryValue = (queryValue) => {
    this.setState({query: queryValue});
    toggleSearchResultsContainer(queryValue);
    if (queryValue === '') {
      updateSearchQuery('');
    }
  };

  onSuggestionSelected = (event, { suggestion }) => {
    this.setQueryValue(suggestion.query);
  };

  onSuggestionCleared = () => {
    this.setQueryValue('');
  };

  onChange = (newValue) => {
    this.setQueryValue(newValue);
  };

  renderSuggestionsContainer = ({ containerProps, children, query }) => (
    <div {...containerProps}>
      {<span className="trending-title">{Drupal.t('Trending searches')}</span>}
      {children}
    </div>
  );

  render() {
    const { query } = this.state;
    // Display search results when wrapper is present on page.
    const searchWrapper = document.getElementById('alshaya-algolia-search');
    const searchResultsDiv = (typeof searchWrapper != 'undefined' && searchWrapper != null) && query !== ''
      ? (<SearchResultsRender query={query} />)
      : '';

    return (
      <div>
        <InstantSearch indexName={ `${drupalSettings.algoliaSearch.indexName}_query` } searchClient={searchClient}>
          <Configure hitsPerPage={drupalSettings.autocomplete.hits}/>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            renderSuggestionsContainer={this.renderSuggestionsContainer}
            onChange={this.onChange}
            currentValue={query}
          />
        </InstantSearch>
        {isMobile() && (
          <Portal id="top-results" conditional query={query}>
            <span className="top-suggestions-title">{Drupal.t('top suggestions')}</span>
            <InstantSearch indexName={drupalSettings.algoliaSearch.indexName} searchClient={searchClient}>
              <Configure hitsPerPage={drupalSettings.autocomplete.hits} query={query}/>
              <Hits hitComponent={Teaser}/>
            </InstantSearch>
          </Portal>
        )}
        {searchResultsDiv}
      </div>
    );
  }
}

// Start instant search only after Document ready.
(function ($, Drupal) {
  ReactDOM.render(
    <AppAutocomplete />,
    document.querySelector('#alshaya-algolia-autocomplete')
  );
})(jQuery);
