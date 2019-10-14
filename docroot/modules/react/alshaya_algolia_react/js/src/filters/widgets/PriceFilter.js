import React from 'react';
import { connectNumericMenu } from 'react-instantsearch-dom';

function PriceFilter(props) {
  const { items, refine } = props;

  return (
    <ul>
      {items.map(item => (
        <li key={item.value} className={"facet-item " + (item.isRefined ? 'is-active' : '')}>
          <span
            className="facet-item__value"
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            {item.label}
            <span className="facet-item__count"></span>
          </span>
        </li>
      ))}
    </ul>
  );
 }

export default connectNumericMenu(PriceFilter);
