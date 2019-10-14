import React from 'react';
import { connectRefinementList } from 'react-instantsearch-dom';

// RefinementList used commonly for most of filters.
function CommonRefinement(props) {
  const { items, attribute, refine } = props;

  return (
    <ul>
      {items.map(item => (
        <li key={item.label} className={"facet-item " + (item.isRefined ? 'is-active' : '')}>
            {/* <label for={`${attribute}-${item.label}`}> */}
          <span
            className="facet-item__value"
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            {item.label}
            <span className="facet-item__count">({item.count})</span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(CommonRefinement);
