import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import _isEqual  from 'lodash/isEqual';
import CustomHighlight from './CustomHighlight';
import {
  getCurrentSearchQuery,
  isMobile,
  removeLangRedirect,
  getLangRedirect
} from '../../utils';
import Portal from '../portal';

const InputButtons = React.memo((props) => {
  return (
    <React.Fragment>
      <Portal
        key="back-button"
        onclick={(event) => props.backCallback(event)}
        className="algolia-search-back-icon"
        id="react-algolia-searchbar-back-button"
        query=""
      />
      <Portal
        key="clear-button"
        onclick={(event) => props.clearCallback(event)}
        className="algolia-search-cleartext-icon"
        id="react-algolia-searchbar-clear-button"
        query=""
      />
    </React.Fragment>
  );
});


class Autocomplete extends React.Component {
  timerId = null;
  reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');

  constructor(props)  {
    super(props);
    const searchQuery = getCurrentSearchQuery();
    this.state = {
      value: searchQuery !== null && searchQuery !== '' ? searchQuery : props.currentRefinement,
    };
    this.autosuggest = React.createRef();
  }

  shouldComponentUpdate(nextProps, nextState) {
    return (nextProps.currentRefinement !== this.props.currentRefinement || nextState.value !== this.state.value || !_isEqual(nextProps.hits, this.props.hits));
  }

  componentDidMount()  {
    window.addEventListener('popstate', this.onPopState);
    // Change name to search for iphone devices.
    this.autosuggest.current.input.name = 'search';
    // Change type to search for android devices.
    this.autosuggest.current.input.type = 'search';
    this.blurORFocus();
    this.onKeyUp();
    this.showMobileElements();
  }

  componentWillUnmount() {
    window.removeEventListener('popstate', this.onPopState);
  }

  blurORFocus() {
    if (getLangRedirect() == '1') {
      removeLangRedirect();
      this.autosuggest.current.input.focus();
    }
    else {
      this.autosuggest.current.input.blur();
    }
  }

  onPopState = event => {
    let query = getCurrentSearchQuery();
    // Update new value in textinput.
    this.onChange(null, {newValue: query});
    if (Object.keys(query).length == 0) {
      // Remove the focus from text input and remove unnecessary
      // classes.
      this.reactSearchBlock[0].classList.remove('focused', 'clear-icon');
      this.blurORFocus();
    }
  }

  addFocus = () => {
    this.reactSearchBlock[0].classList.add('focused');
    this.showMobileElements(this.state.value);
  };

  onSuggestionsFetchRequested = ({ value }) => {
    if (this.shouldRenderSuggestions(value)) {
      this.props.refine(value);
    }
  };

  onSuggestionsClearRequested = () => {
    this.props.refine();
  };

  onKeyUp = () => {
    if (this.state.value.length < 1) {
      this.reactSearchBlock[0].classList.remove('clear-icon');
    }
    else {
      this.reactSearchBlock[0].classList.add('clear-icon');
    }
  };

  showMobileElements = (newvalue = '') => {
    const valueToCheck = (newvalue !== '') ? newvalue : this.state.value;

    if (valueToCheck !== '') {
      this.reactSearchBlock[0].classList.add('clear-icon');
      if (isMobile()) {
        this.reactSearchBlock[0].classList.add('show-algolia-search-bar');
      }
    }
    else if (valueToCheck === '') {
      this.reactSearchBlock[0].classList.remove('clear-icon');
    }
  }

  // On change send value to parent component to update search results.
  onChange = (event, { newValue }) => {
    if (!newValue) {
      this.props.onSuggestionCleared();
    }

    // Wait for sometime for user to finish typing, before we do update
    // query and do api call to algolia.
    clearTimeout(this.timerId);
    const inputTag = this.autosuggest.current.input;
    this.timerId = setTimeout(() => {
      this.props.refine(newValue);
      this.props.onChange(newValue, inputTag);
    }, 100);

    this.setState({
      value: newValue,
    });
    this.showMobileElements(newValue);
  };

  onSubmitCall = event => {
    event.preventDefault();
    event.stopPropagation();
    this.autosuggest.current.input.blur();
    return false;
  }

  getSuggestionValue(hit) {
    return hit.query;
  }

  renderSuggestion(hit) {
    return (<CustomHighlight attribute="query" hit={hit} />)
  }

  shouldRenderSuggestions(value) {
    // Display trending searches for desktop on when searchbox is empty.
    // otherwise show it only for mobile always.
    return (value.trim() === '') || (window.innerWidth < 768);
  }

  clearSearchFieldInput = (event) => {
    // Empty State & Input.
    this.reactSearchBlock[0].classList.remove('clear-icon');
    // Clear sate value and suggestions.
    this.setState({value: ''});
    this.onSuggestionsClearRequested();
    // Set query to empty to hide the search results and update the browser hash.
    this.props.onChange('');
    // Keep focus.
    this.autosuggest.current.input.focus();
  };

  backIconClickEvent = (event) => {
    this.reactSearchBlock[0].classList.remove('show-algolia-search-bar');
    let mobileSearchInNav = document.getElementsByClassName('search-active');
    if (mobileSearchInNav.length !== 0) {
      mobileSearchInNav[0].classList.remove('search-active');
    }
    this.reactSearchBlock[0].classList.remove('focused');
  };

  renderSuggestionsContainer = ({ containerProps, children, query }) => (
    <div {...containerProps}>
      {<span className="trending-title">{Drupal.t('Trending searches')}</span>}
      {children}
    </div>
  );

  render() {
    const { hits, onSuggestionSelected } = this.props;
    const { value } = this.state;

    const inputProps = {
      placeholder: Drupal.t('Search', {}, {'context': "algolia_search_block_placeholder"}),
      onChange: this.onChange,
      onFocus: () => this.addFocus(),
      onKeyUp: this.onKeyUp,
      value,
    };

    return (
      <React.Fragment>
        <form action="#" onSubmit={event => this.onSubmitCall(event)}>
        <Autosuggest
          ref={this.autosuggest}
          suggestions={hits}
          onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
          onSuggestionsClearRequested={this.onSuggestionsClearRequested}
          onSuggestionSelected={onSuggestionSelected}
          getSuggestionValue={this.getSuggestionValue}
          renderSuggestionsContainer={this.renderSuggestionsContainer}
          renderSuggestion={this.renderSuggestion}
          shouldRenderSuggestions={this.shouldRenderSuggestions}
          inputProps={inputProps}
          focusInputOnSuggestionClick={false}
        />
        </form>
        <InputButtons backCallback={this.backIconClickEvent} clearCallback={this.clearSearchFieldInput} />
      </React.Fragment>
    );
  }
}

export default connectAutoComplete(Autocomplete);
