import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import CustomHighlight from './ConnectHighlight';

class Autocomplete extends React.Component {
  state = {
    value: this.props.currentRefinement,
  };

  onChange = (event, { newValue }) => {
    if (!newValue) {
      this.props.onSuggestionCleared();
    }

    this.setState({
      value: newValue,
    });
  };

  onSuggestionsFetchRequested = ({ value }) => {
    this.props.refine(value);
  };

  onSuggestionsClearRequested = () => {
    this.props.refine();
  };

  getSuggestionValue(hit) {
    return hit.query;
  }

  suggestionTemplate(suggestion) {
    return `<span className="suggested-text">${suggestion._highlightResult.query.value}</span><span className="populate-input">&#8598;</span>s`
  }

  renderSuggestion(hit) {
    console.log(hit)
    // const [category] = hit.instant_search.facets.exact_matches.categories;

    return (<CustomHighlight attribute="query" hit={hit} />)

    // return (
    //   <span>
    //     <Highlight attribute="query" hit={hit} tagName="mark" />
    //   </span>
    // );
  }

  render() {
    const { hits, onSuggestionSelected, renderSuggestionsContainer } = this.props;
    const { value } = this.state;

    const inputProps = {
      placeholder: Drupal.t('What are you looking for?'),
      onChange: this.onChange,
      value,
    };

    return (
      <Autosuggest
        suggestions={hits}
        onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
        onSuggestionsClearRequested={this.onSuggestionsClearRequested}
        onSuggestionSelected={onSuggestionSelected}
        getSuggestionValue={this.getSuggestionValue}
        renderSuggestionsContainer={renderSuggestionsContainer}
        renderSuggestion={this.renderSuggestion}
        inputProps={inputProps}
      />
    );
  }
}

export default connectAutoComplete(Autocomplete);
