import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import CustomHighlight from './CustomHighlight';

class Autocomplete extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      value: props.currentValue !== null && props.currentValue !== '' ? props.currentValue : this.props.currentRefinement,
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({ value: nextProps.currentValue });
  }

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
        shouldRenderSuggestions={this.shouldRenderSuggestions}
        inputProps={inputProps}
      />
    );
  }
}

export default connectAutoComplete(Autocomplete);
