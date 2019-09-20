import React from 'react';
import ReactDOM from 'react-dom';
import AutoComplete from './Components/Autocomplete';
import SearchResults from './Components/SearchResults';
import InstantSearchComponent from './Components/InstantSearchComponent';

class AppAutocomplete extends React.Component {
  state = {
    query: ''
  };

  onSuggestionSelected = (event, { suggestion }) => {
    this.setState({
      query: suggestion.query,
    });
  }

  onSuggestionCleared = () => {
    this.setState({
      query: '',
    });
  }

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
  )

  render() {
    const { query, categories } = this.state;

    return (
      <div>
        <InstantSearchComponent indexName={ `${drupalSettings.algoliaSearch.indexName}_query` }>
          <AutoComplete
            onSuggestionSelected={this.onSuggestionSelected}
            onSuggestionCleared={this.onSuggestionCleared}
            renderSuggestionsContainer={this.renderSuggestionsContainer}
            onChange={this.onChange}
          />
        </InstantSearchComponent>
        <SearchResults query={query} />
      </div>
    );
  }
}

ReactDOM.render(
  <AppAutocomplete />,
  document.querySelector('#alshaya-algolia-autocomplete')
);
