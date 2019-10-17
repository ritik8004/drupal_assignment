import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import CustomHighlight from './components/algolia/CustomHighlight';

class Autocomplete extends React.Component {
  reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');

  constructor(props) {
    super(props);
    this.state = {
      value: props.currentValue !== null && props.currentValue !== '' ? props.currentValue : this.props.currentRefinement,
    };
  }

  // To update component when property change.
  componentWillReceiveProps(nextProps) {
    this.setState({ value: nextProps.currentValue });
  }

  toggleFocus = (action) => {
    this.reactSearchBlock[0].classList[action]('focused');
  };

  onKeyUp = () => {
    if (this.state.value.length < 1) {
      this.reactSearchBlock[0].classList.remove('clear-icon');
    }
    else {
      this.reactSearchBlock[0].classList.add('clear-icon');
    }
  };

  // On change send value to parent component to update search results.
  onChange = (event, { newValue }) => {
    if (!newValue) {
      this.props.onSuggestionCleared();
    }
    this.props.refine(newValue);
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
    return (<CustomHighlight attribute="query" hit={hit} suffix={<span className="populate-input">&#8598;</span>} />)
  }

  shouldRenderSuggestions(value) {
    // Display trending searches for desktop on when searchbox is emty.
    // otherwise show it only for mobile always.
    return (value === '') || (window.innerWidth < 768);
  }

  render() {
    const { hits, onSuggestionSelected, renderSuggestionsContainer } = this.props;
    const { value } = this.state;

    const inputProps = {
      placeholder: Drupal.t('Search', {}, {'context': "algolia_search_block_placeholder"}),
      onChange: this.onChange,
      onFocus: () => this.toggleFocus('add'),
      onBlur: () => this.toggleFocus('remove'),
      onKeyUp: this.onKeyUp,
      value,
    };

    return (
      <React.Fragment>
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
      </React.Fragment>
    );
  }
}

export default connectAutoComplete(Autocomplete);
