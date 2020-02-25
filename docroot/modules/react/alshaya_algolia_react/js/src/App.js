import React from 'react';
import { InstantSearch } from 'react-instantsearch-dom';
import { Configure, Hits } from "react-instantsearch-dom";
import AutoComplete from './components/algolia/Autocomplete';
import SearchResults from './components/searchresults';
import Portal from './components/portal';
import Teaser from './components/teaser';
import {
  getCurrentSearchQuery,
  isMobile,
  redirectToOtherLang,
  setSearchQuery
} from './utils';
import {algoliaSearchClient} from "./config/SearchClient";

if (window.NodeList && !NodeList.prototype.forEach) {
  NodeList.prototype.forEach = Array.prototype.forEach;
}

class App extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      query: getCurrentSearchQuery()
    };
  };

  componentDidMount() {
    if (this.state.query !== '') {
      redirectToOtherLang(this.state.query);
    }
  }

  setQueryValue = (queryValue, inputTag = null) => {
    this.setState({query: queryValue});
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
    const searchResultsDiv = (typeof searchWrapper != 'undefined' && searchWrapper != null)
      ? (<SearchResults query={query} />)
      : '';

    return (
      <div>
        <InstantSearch indexName={ `${drupalSettings.algoliaSearch.indexName}_query` } searchClient={algoliaSearchClient}>
          <Configure hitsPerPage={drupalSettings.autocomplete.hits}/>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            onChange={this.onChange}
          />
        </InstantSearch>
        {isMobile() && (
          <Portal id="top-results" conditional query={query}>
            <span className="top-suggestions-title">{Drupal.t('top suggestions')}</span>
            <InstantSearch indexName={drupalSettings.algoliaSearch.indexName} searchClient={algoliaSearchClient}>
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

export default App;
