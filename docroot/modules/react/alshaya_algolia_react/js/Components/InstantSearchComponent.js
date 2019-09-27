import React from 'react';
import {
  InstantSearch,
  Configure
} from 'react-instantsearch-dom';
import {searchClient} from '../Config/SearchClient';

const InstantSearchComponent = ({ children, indexName}) => (
  <InstantSearch searchClient={searchClient} indexName={indexName}>
    {children}
    <Configure hitsPerPage="6"/>
  </InstantSearch>
);

export default InstantSearchComponent;
