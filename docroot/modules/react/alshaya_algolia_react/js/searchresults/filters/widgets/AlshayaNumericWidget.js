import React from 'react';
import { connectNumericMenu } from 'react-instantsearch-dom';

function AlshayaNumericWidget({ items, refine }) {
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

export default connectNumericMenu(AlshayaNumericWidget);
