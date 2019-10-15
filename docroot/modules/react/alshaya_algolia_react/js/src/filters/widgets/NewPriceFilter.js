import React from 'react';
import connectWithPriceFilter from './connectors/connectWithPriceFilter';
import { getPriceRangeLabel } from '../../utils/PriceHelper';

const NewPriceFilter = (props) => {
  const { items, attribute, refine } = props;

  return (
    <ul>
      {items.map(item => (
        <li key={item.label} className={"facet-item " + (item.isRefined ? 'is-active' : '')}>
          <span
            className="facet-item__value"
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            {getPriceRangeLabel(item.value)}
            <span className="facet-item__count">({item.count})</span>
          </span>
        </li>
      ))}
    </ul>
  );
}

export default connectWithPriceFilter(NewPriceFilter);

