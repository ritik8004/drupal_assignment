import React from 'react';
import connectRefinementList from '../connectors/connectRefinementList';

// RefinementList used commonly for most of filters.
function CommonRefinement(props) {
  const { items, attribute, refine } = props;

  if (typeof props.itemCount != 'undefined') {
    props.itemCount(attribute, items.length);
  }

  return (
    <ul>
      {items.map(item => (
        <li key={item.label}
            className={"facet-item " + (item.isRefined ? 'is-active' : '')}
            datadrupalfacetlabel={props.name}
            onClick={event => {
              refine(item.value);

            }}
        >
            {/* <label for={`${attribute}-${item.label}`}> */}
          <span className="facet-item__value">
            {item.label}
            <span className="facet-item__count">({item.count})</span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectRefinementList(CommonRefinement);
