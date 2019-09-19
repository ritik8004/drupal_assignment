import React from 'react';
import ReactDOM from 'react-dom';
import {
  InstantSearch,
  Configure,
  Hits,
  connectAutoComplete,
  connectRefinementList,
  Highlight,
} from 'react-instantsearch-dom';
import {searchClient} from './Config/SearchClient'
import AutoComplete from './Component/Autocomplete'

class AppAutocomplete extends React.Component {
  state = {
    query: ''
  }

  onSuggestionSelected = (event, { suggestion }) => {
    this.setState({
      query: suggestion.query,
    });
  };

  onSuggestionCleared = () => {
    this.setState({
      query: '',
    });
  }

  renderSuggestionsContainer = ({ containerProps, children, query }) => (
    <div {...containerProps}>
      {children}
      {
        <div className="footer">
          Press Enter to search <strong>{query}</strong>
        </div>
      }
    </div>
  );


  render() {
    return (
      <InstantSearch
        searchClient={searchClient}
        indexName={ `${drupalSettings.algoliaSearch.indexName}_query` }>
        <AutoComplete
          onSuggestionSelected={this.onSuggestionSelected}
          onSuggestionCleared={this.onSuggestionCleared}
          renderSuggestionsContainer={this.renderSuggestionsContainer}
        />
      </InstantSearch>
    );
  }
}

ReactDOM.render(
  <AppAutocomplete />,
  document.querySelector('#alshaya-algolia-autocomplete')
);
