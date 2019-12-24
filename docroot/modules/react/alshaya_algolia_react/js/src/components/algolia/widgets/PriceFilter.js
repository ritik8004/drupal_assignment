import React from 'react';
import connectWithPriceFilter from '../connectors/connectWithPriceFilter';
import { getPriceRangeLabel } from '../../../utils';

const PriceFilter = (props) => {
  const { items, refine } = props;

  if (typeof props.itemCount != 'undefined') {
    props.itemCount(props.attribute, items.length);
  }

  return (
    <ul>
      {items.map(item => (
        <li
          key={item.label}
          className={"facet-item " + (item.isRefined ? 'is-active' : '')}
          onClick={event => {
            event.preventDefault();
            refine(item.value);
          }}
        >
          <span className="facet-item__value">
            {getPriceRangeLabel(item.label)}
            <span className="facet-item__count">({item.count})</span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectWithPriceFilter(PriceFilter);

