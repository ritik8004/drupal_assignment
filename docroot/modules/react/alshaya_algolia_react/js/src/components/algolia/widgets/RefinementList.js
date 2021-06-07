import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// RefinementList used commonly for most of filters.
function CommonRefinement(props) {
  const {
    items, attribute, refine, itemCount,
  } = props;

  if (typeof itemCount !== 'undefined') {
    // Initially the count was updated when the filter
    // gets hide-facet-block class asynchronously.
    // Now, the count for the filter is updated
    // once markup is available.
    setTimeout(() => {
      itemCount(attribute, items.length);
    }, 1);
  }

  return (
    <ul>
      {items.map((item) => (
        <li
          key={item.label}
          className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
          datadrupalfacetlabel={props.name}
          onClick={() => {
            refine(item.value);
          }}
        >
          {/* <label for={`${attribute}-${item.label}`}> */}
          <span className="facet-item__value">
            {item.label}
            <span className="facet-item__count">
              (
              {item.count}
              )
            </span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(CommonRefinement);
