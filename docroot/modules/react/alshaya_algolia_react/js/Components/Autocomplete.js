import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import CustomHighlight from './CustomHighlight';

class Autocomplete extends React.Component {
  state = {
    value: this.props.currentRefinement,
  };

  onFocusIn = () => {
    // Add a class on block so we can show back and clear icons.
    let reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');
    reactSearchBlock[0].classList.add('focused');
  };

  onFocusOut = () => {
    // On focus out, remove the class.
    let reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');
    reactSearchBlock[0].classList.remove('focused');
  };

  onKeyUp = () => {
    let reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');
    if (this.state.value.length < 1) {
      reactSearchBlock[0].classList.remove('clear-icon');
    }
    else {
      reactSearchBlock[0].classList.add('clear-icon');
    }
  };

  onChange = (event, { newValue }) => {
    if (!newValue) {
      this.props.onSuggestionCleared();
    }
    this.props.onChange(newValue);

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

  renderSuggestion(hit) {
    return (<CustomHighlight attribute="query" hit={hit} />)
  }

  shouldRenderSuggestions(value) {
    return true;
  }

  clearSearchFieldInput = (element) => {
    // Empty State & Input.
    this.setState({value: ''});
    let searchInput = document.getElementsByClassName('react-autosuggest__input');
    // Keep focus.
    searchInput[0].focus();
  };

  render() {
    const { hits, onSuggestionSelected, renderSuggestionsContainer } = this.props;
    const { value } = this.state;

    const inputProps = {
      placeholder: Drupal.t('What are you looking for?'),
      onChange: this.onChange,
      onFocus: this.onFocusIn,
      onBlur: this.onFocusOut,
      onKeyUp: this.onKeyUp,
      value,
    };

    return (
      <React.Fragment>
        <span className="algolia-search-back-icon"></span>
        <Autosuggest
          suggestions={hits}
          onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
          onSuggestionsClearRequested={this.onSuggestionsClearRequested}
          onSuggestionSelected={onSuggestionSelected}
          getSuggestionValue={this.getSuggestionValue}
          renderSuggestionsContainer={renderSuggestionsContainer}
          renderSuggestion={this.renderSuggestion}
          shouldRenderSuggestions={this.shouldRenderSuggestions}
          inputProps={inputProps}
        />
        <span className="algolia-search-cleartext-icon" onClick={this.clearSearchFieldInput}></span>
      </React.Fragment>
    );
  }
}

export default connectAutoComplete(Autocomplete);
