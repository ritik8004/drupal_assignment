import React from 'react';
import {
  InstantSearch,
  Configure
} from 'react-instantsearch-dom';

import {searchClient} from '../config/SearchClient';

const InstantSearchComponent = ({ children, indexName}) => (
  <InstantSearch searchClient={searchClient} indexName={indexName}>
    <Configure clickAnalytics />
    {children}
  </InstantSearch>
);

export default InstantSearchComponent;
