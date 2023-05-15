import React from 'react';
import { connectAutoComplete } from 'react-instantsearch-dom';
import Autosuggest from 'react-autosuggest';
import _isEqual from 'lodash/isEqual';
import CustomHighlight from './CustomHighlight';
import {
  getCurrentSearchQuery,
  isMobile,
  removeLangRedirect,
  getLangRedirect,
} from '../../utils';
import Portal from '../portal';

// For checking state of predictiveSearch.
const { predictiveSearchEnabled } = drupalSettings.algoliaSearch;
const clearText = predictiveSearchEnabled ? Drupal.t('Clear') : null;
const InputButtons = React.memo((props) => (
  <>
    {(predictiveSearchEnabled && props.hits.length < 1 && props.value.trim() !== '') ? (
      <Portal
        key="no-result"
        className="predictive-search__msg-no-result"
        innerHTML={`${Drupal.t('Nothing found for "')}<span class="bold">${props.value}"</span>`}
      />
    ) : null}
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
      className={`algolia-search-cleartext-icon ${predictiveSearchEnabled ? 'predictive-search__btn-clear' : ''}`}
      id="react-algolia-searchbar-clear-button"
      query=""
      innerHTML={clearText}
    />
    { predictiveSearchEnabled ? (
      <Portal
        key="predictive-close-button"
        className="predictive-search__btn-close"
        onclick={(event) => props.closeCallback(event)}
      />
    ) : null}
  </>
));

class Autocomplete extends React.Component {
  timerId = null;

  reactSearchBlock = document.getElementsByClassName('block-alshaya-algolia-react-autocomplete');

  constructor(props) {
    super(props);
    const searchQuery = getCurrentSearchQuery();
    this.state = {
      value: searchQuery !== null && searchQuery !== '' ? searchQuery : props.currentRefinement,
    };
    this.autosuggest = React.createRef();
  }

  componentDidMount() {
    window.addEventListener('popstate', this.onPopState);
    // Change name to search for iphone devices.
    this.autosuggest.current.input.name = 'search';
    // Change type to search for android devices.
    this.autosuggest.current.input.type = 'search';
    this.blurORFocus();
    this.onKeyUp();
    this.showMobileElements();
  }

  shouldComponentUpdate(nextProps, nextState) {
    const { currentRefinement, hits } = this.props;
    const { value } = this.state;
    return (
      nextProps.currentRefinement !== currentRefinement
      || nextState.value !== value
      || !_isEqual(nextProps.hits, hits)
    );
  }

  componentWillUnmount() {
    window.removeEventListener('popstate', this.onPopState);
  }

  getSuggestionValue = (hit) => (
    hit.query
  );

  renderSuggestion = (hit) => (
    <CustomHighlight attribute="query" hit={hit} />
  );

  shouldRenderSuggestions = (value) => (
    // Display trending searches for desktop on when searchbox is empty.
    // otherwise show it only for mobile always.
    // Or Show it when predictive search is enabled.
    (value.trim() === '') || (window.innerWidth < 768) || predictiveSearchEnabled
  );

  blurORFocus() {
    if (getLangRedirect() === '1') {
      removeLangRedirect();
      this.autosuggest.current.input.focus();
    } else {
      this.autosuggest.current.input.blur();
    }
  }

  onPopState = () => {
    const query = getCurrentSearchQuery();
    // Update new value in textinput.
    this.onChange(null, { newValue: query });
    if (Object.keys(query).length === 0) {
      // Remove the focus from text input and remove unnecessary
      // classes.
      this.reactSearchBlock[0].classList.remove('focused', 'clear-icon');
      this.blurORFocus();
    }
  }

  addFocus = () => {
    const { value } = this.state;
    this.reactSearchBlock[0].classList.add('focused');
    // Toggle clear-icon & show-algolia-search-bar(mobile) class on enter and not on change event.
    this.showMobileElements(value);
    const searchComponent = document.getElementsByClassName('predictive-search');
    if (searchComponent.length !== 0) {
      searchComponent[0].classList.add('predictive-search--open');
      document.body.classList.add('show-predictive-search');
    }
  };

  onSuggestionsFetchRequested = ({ value }) => {
    const { refine } = this.props;
    if (this.shouldRenderSuggestions(value)) {
      refine(value);
    }
  };

  onSuggestionsClearRequested = () => {
    const { refine } = this.props;
    refine();
  };

  onKeyUp = () => {
    const { value } = this.state;
    if (value.length < 1) {
      this.reactSearchBlock[0].classList.remove('clear-icon');
    } else {
      this.reactSearchBlock[0].classList.add('clear-icon');
    }
  };

  showMobileElements = (newvalue = '') => {
    const { value } = this.state;
    const valueToCheck = (newvalue !== '') ? newvalue : value;

    if (valueToCheck !== '') {
      this.reactSearchBlock[0].classList.add('clear-icon');
      // Add class for mobile Or when predictive search is enabled.
      if (isMobile() || predictiveSearchEnabled) {
        this.reactSearchBlock[0].classList.add('show-algolia-search-bar');
      }
    } else if (valueToCheck === '') {
      this.reactSearchBlock[0].classList.remove('clear-icon');
    }
  }

  clearAllFilters = () => {
    const clearFilter = document.querySelector('#alshaya-algolia-search #clear-filter');
    if (clearFilter) {
      clearFilter.click();
    }
  }

  // On change send value to parent component to update search results.
  onChange = (event, { newValue }) => {
    // Store input value for predictiveSearch.
    const {
      onSuggestionCleared, refine, onChange, inputSearchValue,
    } = this.props;
    if (!newValue) {
      onSuggestionCleared();
    }

    // Wait for sometime for user to finish typing, before we do update
    // query and do api call to algolia.
    clearTimeout(this.timerId);
    const inputTag = this.autosuggest.current.input;
    this.clearAllFilters();
    this.timerId = setTimeout(() => {
      // Trending searches only appear on Web desktop when the input is focused.
      // We do not need to request the query-index on desktop web when value is changed.
      // Change should request query-index only for web on mobile and for predictive search.
      if (window.innerWidth < 768 || predictiveSearchEnabled) {
        refine(newValue);
      }
      // Search results to be shown on formSubmit in case on predictiveSearch.
      if (!predictiveSearchEnabled) {
        onChange(newValue, inputTag);
      }
    }, 100);

    this.setState({
      value: newValue,
    });
    // In predictive search query is updated only on form submit, we need to store keyword.
    inputSearchValue(newValue);
    this.showMobileElements(newValue);
  };

  onSubmitCall = (event) => {
    event.preventDefault();
    event.stopPropagation();
    this.autosuggest.current.input.blur();
    if (predictiveSearchEnabled) {
      const { value } = this.state;
      const { onChange } = this.props;
      const inputTag = this.autosuggest.current.input;
      onChange(value, inputTag);
      // Remove class for closing overlay on form submit.
      const predictiveSearchComponent = document.getElementsByClassName('predictive-search');
      if (predictiveSearchComponent.length !== 0
        && predictiveSearchComponent[0].classList.contains('predictive-search--open')) {
        predictiveSearchComponent[0].classList.remove('predictive-search--open');
      }
    }
    return false;
  }

  clearSearchFieldInput = () => {
    const { onChange } = this.props;
    // Empty State & Input.
    this.reactSearchBlock[0].classList.remove('clear-icon');
    // Clear sate value and suggestions.
    this.setState({ value: '' });
    this.onSuggestionsClearRequested();
    // Set query to empty to hide the search results and update the browser hash.
    onChange('');
    // Keep focus.
    this.autosuggest.current.input.focus();
    // Clear filters if clicked on cross button.
    this.clearAllFilters();
  };

  backIconClickEvent = () => {
    const { onChange } = this.props;
    this.reactSearchBlock[0].classList.remove('show-algolia-search-bar');
    const mobileSearchInNav = document.getElementsByClassName('search-active');
    if (mobileSearchInNav.length !== 0) {
      mobileSearchInNav[0].classList.remove('search-active');
    }
    // Clear sate value and suggestions.
    this.setState({ value: '' });
    this.onSuggestionsClearRequested();
    // Set query to empty to hide the search results and update the browser hash.
    onChange('');
    this.reactSearchBlock[0].classList.remove('focused');
  };

  // Removing "predictive-search--open" on click of close button only for predictive search.
  closePredictiveSearchEvent = () => {
    const predictiveSearchComponent = document.getElementsByClassName('predictive-search');
    if (predictiveSearchComponent.length !== 0) {
      predictiveSearchComponent[0].classList.remove('predictive-search--open');
      document.body.classList.remove('show-predictive-search');
    }
  };

  renderSuggestionsContainer = ({ containerProps, children }) => (
    <div {...containerProps}>
      {<span className="trending-title">{Drupal.t('Trending searches')}</span>}
      {children}
    </div>
  );

  render() {
    const { hits, onSuggestionSelected } = this.props;
    const { value } = this.state;

    const inputProps = {
      placeholder: Drupal.t('Search', {}, { context: 'alshaya_static_text|algolia_search_block_placeholder' }),
      onChange: this.onChange,
      onFocus: () => this.addFocus(),
      onKeyUp: this.onKeyUp,
      value,
    };

    return (
      <>
        <form action="#" onSubmit={(event) => this.onSubmitCall(event)}>
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
        <InputButtons
          backCallback={this.backIconClickEvent}
          clearCallback={this.clearSearchFieldInput}
          closeCallback={this.closePredictiveSearchEvent}
          hits={hits}
          value={value}
        />
      </>
    );
  }
}

export default connectAutoComplete(Autocomplete);
