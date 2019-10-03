import React from 'react';
import {
  InstantSearch,
} from 'react-instantsearch-dom';
import {searchClient} from '../config/SearchClient';

const InstantSearchComponent = ({ children, indexName}) => (
  <InstantSearch searchClient={searchClient} indexName={indexName}>{children}</InstantSearch>
);

export default InstantSearchComponent;
