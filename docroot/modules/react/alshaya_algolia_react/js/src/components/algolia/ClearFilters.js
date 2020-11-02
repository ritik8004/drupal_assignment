import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import _uniqBy from 'lodash/uniqBy';

export default connectCurrentRefinements(({
  title, items, refine, pageType = null,
}) => {
  // For PLP mobile view, we don't want to remove the category filter.
  const filteredItems = (pageType === 'plp')
    ? _uniqBy(items, 'attribute').filter((value) => value.attribute.indexOf('lhn_category') < 0)
    : _uniqBy(items, 'attribute');

  return (
    <a
      href="#"
      id="clear-filter"
      onClick={(event) => {
        event.preventDefault();
        refine(filteredItems);
      }}
    >
      {title}
    </a>
  );
});
