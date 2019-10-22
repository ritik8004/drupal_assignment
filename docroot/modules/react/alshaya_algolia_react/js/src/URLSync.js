import React, { Component } from 'react';
import qs from 'qs';
import { updateAfter, getCurrentSearchQueryString, updateSearchQuery } from './utils';

const searchStateToURL = searchState => {
  return searchState.query ? qs.stringify(searchState) : '';
}

const withURLSync = SearchResults =>
  class WithURLSync extends Component {
    constructor(props) {
      super(props);
      let searchState = getCurrentSearchQueryString();
      delete searchState.page;
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

    onPopState = ({ state }) => {
      this.setState({
        searchState: state || {},
      });
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

      clearTimeout(this.debouncedSetState);
      this.debouncedSetState = setTimeout(() => {
        updateSearchQuery(searchStateToURL(searchState));
      }, updateAfter);

      this.setState({ searchState });
    };

    render() {
      const { searchState } = this.state;

      return (
        <SearchResults
          {...this.props}
          searchState={searchState}
          onSearchStateChange={this.onSearchStateChange}
          createURL={searchStateToURL}
        />
      );
    }
  };

export default withURLSync;
