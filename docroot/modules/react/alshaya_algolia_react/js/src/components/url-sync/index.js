import React, { Component } from 'react';
import {
  updateAfter,
  getCurrentSearchQueryString,
  updateSearchQuery,
  searchStateToURL,
  toggleSearchResultsContainer
} from '../../utils';

const withURLSync = SearchResultsComponent =>
  class WithURLSync extends Component {
    constructor(props) {
      super(props);
      let searchState = getCurrentSearchQueryString();
      delete searchState.page;
      if (!Object.keys(searchState).length) {
        searchState = {'query': props.query};
        this.updateBrowserHash({'query': props.query});
      }
      this.state = {
        searchState: searchState,
      };
    }

    componentDidMount() {
      window.addEventListener('popstate', this.onPopState);
    }

    componentWillUnmount() {
      clearTimeout(this.debouncedSetState);
      window.removeEventListener('popstate', this.onPopState);
    }

    onPopState = event => {
      let state = getCurrentSearchQueryString();
      if (Object.keys(state).length > 0) {
        this.setState({
          searchState: state || {},
        });
      }
      else {
        toggleSearchResultsContainer('');
      }
    }

    onSearchStateChange = searchState => {
      searchState.query = searchState.configure.query;
      // Configure contains internal settings like oos filter, results per
      // page etc.. we don't want to pass it in query string.
      delete searchState.configure;
      // We do want to clear the filters and do not want to show querystring
      // in addressbar, when there are no search query.
      if (searchState.query.trim() === '') {
        searchState = {};
      }
      this.updateBrowserHash(searchState);
      this.setState({ searchState });
    };

    updateBrowserHash = (searchState) => {
      clearTimeout(this.debouncedSetState);
      this.debouncedSetState = setTimeout(() => {
        updateSearchQuery(searchStateToURL(searchState));
      }, updateAfter);
    }

    render() {
      const { searchState } = this.state;

      return (
        <SearchResultsComponent
          {...this.props}
          searchState={searchState}
          onSearchStateChange={this.onSearchStateChange}
          createURL={searchStateToURL}
        />
      );
    }
  };

export default withURLSync;
