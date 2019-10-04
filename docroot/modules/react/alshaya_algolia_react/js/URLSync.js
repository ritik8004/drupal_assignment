import React, { Component } from 'react';
import qs from 'qs'
import { updateAfter, getCurrentSearchQueryString, updateSearchQuery } from './utils/utils';

const searchStateToURL = searchState => {
  return searchState.query ? qs.stringify(searchState) : '';
}

const withURLSync = SearchResults =>
  class WithURLSync extends Component {
    constructor(props) {
      super(props);
      this.state = {
        searchState: getCurrentSearchQueryString(),
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
      // We do want to clear the filters and do not want to show querystring
      // in addressbar, when there are no search query.
      if (searchState.query === '') {
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
