import React from 'react';
import connectWithPriceFilter from '../connectors/connectWithPriceFilter';
import { getPriceRangeLabel } from '../../../utils';

const PriceFilter = (props) => {
  const { items, itemCount, refine } = props;

  if (typeof itemCount !== 'undefined') {
    setTimeout(() => {
      itemCount(props.attribute, items.length);
    }, 1);
  }

  return (
    <ul>
      {items.map((item) => (
        <li
          key={item.label}
          className={`facet-item ${item.isRefined ? 'is-active' : ''}`}
          datadrupalfacetlabel={props.name}
          onClick={(event) => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="facet-item__value">
            {getPriceRangeLabel(item.label)}
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
};

export default connectWithPriceFilter(PriceFilter);
