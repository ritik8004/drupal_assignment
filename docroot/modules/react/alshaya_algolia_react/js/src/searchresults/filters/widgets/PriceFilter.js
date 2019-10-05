import React from 'react';
import { connectNumericMenu } from 'react-instantsearch-dom';

function PriceFilter(props) {
  const { items, refine } = props;

  return (
    <ul>
      {items.map(item => (
        <li key={item.value}>
          <a
            href="#"
            style={{ fontWeight: item.isRefined ? 'bold' : '' }}
            onClick={event => {
              event.preventDefault();
              refine(item.value);
            }}
          >
            {item.label}
          </a>
        </li>
      ))}
    </ul>
  );
 }

export default connectNumericMenu(PriceFilter);
