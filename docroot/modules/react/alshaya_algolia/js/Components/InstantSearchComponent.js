import React from 'react';
import {
  InstantSearch,
} from 'react-instantsearch-dom';
import {searchClient} from '../Config/SearchClient';

class InstantSearchComponent extends React.Component {
  render() {
    return (
      <InstantSearch searchClient={searchClient} indexName={this.props.indexName}>
        {this.props.children}
      </InstantSearch>
    );
  }
}

export default InstantSearchComponent;
