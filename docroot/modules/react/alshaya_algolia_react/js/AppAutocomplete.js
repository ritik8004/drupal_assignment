import React from 'react';
import ReactDOM from 'react-dom';
import AutoComplete from './Components/Autocomplete';
import SearchResults from './Components/SearchResults';
import InstantSearchComponent from './Components/InstantSearchComponent';
import { createBrowserHistory } from 'history';
import queryString from 'query-string'

const history = createBrowserHistory();

class AppAutocomplete extends React.Component {

  constructor(props) {
    super(props);
    const parsedHash = queryString.parse(location.hash);
    this.state = {
      query: parsedHash && parsedHash.q ? parsedHash.q : ''
    };
    this.updateQueryValue = this.updateQueryValue.bind(this);
  }

  componentDidMount() {
    window.addEventListener("hashchange", this.updateQueryValue, false);
  }

  updateQueryValue() {
    const parsedHash = queryString.parse(location.hash);
    if (parsedHash && parsedHash.q) {
      this.setQueryValue(parsedHash.q);
    }
  }

  setQueryValue(queryValue) {
    this.setState({query: queryValue});
    // Push query to browser histroy to ga back and see previous results.
    history.push({hash: `q=${queryValue}`});
  }

  onSuggestionSelected = (event, { suggestion }) => {
    this.setQueryValue(suggestion.query);
  }

  onSuggestionCleared = () => {
    this.setQueryValue('');
  }

  onChange = (newValue) => {
    this.setQueryValue(newValue);
  };

  renderSuggestionsContainer = ({ containerProps, children, query }) => (
    <div {...containerProps}>
      {<span className="trending-title">Trending searches</span>}
      {children}
    </div>
  )

  render() {
    const { query, categories } = this.state;
    // Display search results when wrapper is present on page.
    const searchWrapper = document.getElementById('alshaya-algolia-search');
    const searchResultsDiv = (typeof searchWrapper != 'undefined' && searchWrapper != null) ? (<SearchResults query={query} />) : '';

    return (
      <div>
        <InstantSearchComponent indexName={ `${drupalSettings.algoliaSearch.indexName}_query` }>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            renderSuggestionsContainer={this.renderSuggestionsContainer}
            onChange={this.onChange}
            currentValue={query}
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
