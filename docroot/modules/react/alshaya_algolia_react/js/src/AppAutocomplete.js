import React from 'react';
import ReactDOM from 'react-dom';
import { InstantSearch } from 'react-instantsearch-dom';
import { Configure } from "react-instantsearch-dom";
import qs from 'qs'
import {searchClient} from './config/SearchClient';
import AutoComplete from './Autocomplete';
import { toggleSearchResultsContainer } from './searchresults/SearchUtility';
import SearchResultsRender from './searchresults/SearchResultsRender';
import { getCurrentSearchQuery } from './utils/utils';

class AppAutocomplete extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      query: getCurrentSearchQuery()
    };
    toggleSearchResultsContainer(this.state.query);
    this.updateQueryValue = this.updateQueryValue.bind(this);
  };

  componentDidMount() {
    window.addEventListener('hashchange', this.updateQueryValue, false);
  };

  updateQueryValue() {
    const parsedHash = qs.parse(location.hash);
    if (parsedHash && parsedHash.query) {
      this.setQueryValue(parsedHash.query);
    }
  };

  setQueryValue(queryValue) {
    this.setState({query: queryValue});
    toggleSearchResultsContainer(queryValue);
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
      {<span className="trending-title">Trending searches</span>}
      {children}
    </div>
  );

  render() {
    const { query, categories } = this.state;
    // Display search results when wrapper is present on page.
    const searchWrapper = document.getElementById('alshaya-algolia-search');
    const searchResultsDiv = (typeof searchWrapper != 'undefined' && searchWrapper != null)
      ? (<SearchResultsRender query={query} />)
      : '';

    return (
      <div>
        <InstantSearch indexName={ `${drupalSettings.algoliaSearch.indexName}_query` } searchClient={searchClient}>
          <Configure hitsPerPage="{drupalSettings.autocomplete.hits}"/>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            renderSuggestionsContainer={this.renderSuggestionsContainer}
            onChange={this.onChange}
            currentValue={query}
          />
        </InstantSearch>
        {searchResultsDiv}
      </div>
    );
  }
}

ReactDOM.render(
  <AppAutocomplete />,
  document.querySelector('#alshaya-algolia-autocomplete')
);
