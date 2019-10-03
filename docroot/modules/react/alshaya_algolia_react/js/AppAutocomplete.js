import React from 'react';
import ReactDOM from 'react-dom';
import AutoComplete from './Components/Autocomplete';
import SearchResults from './Components/SearchResults';
import InstantSearchComponent from './Components/InstantSearchComponent';
import { Configure } from "react-instantsearch-dom";

class AppAutocomplete extends React.Component {
  state = {
    query: ''
  };

  onSuggestionSelected = (event, { suggestion }) => {
    this.setState({
      query: suggestion.query,
    });
  };

  onSuggestionCleared = () => {
    this.setState({
      query: '',
    });
  };

  onChange = (newValue) => {
    this.setState({
      query: newValue,
    });
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
    const searchResultsDiv = (typeof searchWrapper != 'undefined' && searchWrapper != null) ? (<SearchResults query={query} />) : '';

    return (
      <div>
        <InstantSearchComponent indexName={ `${drupalSettings.algoliaSearch.indexName}_query` }>
          <Configure hitsPerPage="6"/>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            renderSuggestionsContainer={this.renderSuggestionsContainer}
            onChange={this.onChange}
          />
        </InstantSearchComponent>
        {searchResultsDiv}
      </div>
    );
  }
}

ReactDOM.render(
  <AppAutocomplete />,
  document.querySelector('#alshaya-algolia-autocomplete')
);
