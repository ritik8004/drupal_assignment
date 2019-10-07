import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';

export default connectCurrentRefinements(({ items, refine }) => (
  <a
    href="#"
    onClick={event => {
      event.preventDefault();
      refine(items)
    }}
  >
    Clear Filters
  </a>
));
