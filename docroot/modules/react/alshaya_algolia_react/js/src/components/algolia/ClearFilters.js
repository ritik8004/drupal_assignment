import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';

export default connectCurrentRefinements(({ title, items, refine }) => (
  <a
    href="#"
    id="clear-filter"
    onClick={event => {
      event.preventDefault();
      refine(items)
    }}
  >
    {title}
  </a>
));
