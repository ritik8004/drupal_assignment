import React from 'react';
import { connectNumericMenu } from 'react-instantsearch-dom';

function AlshayaNumericWidget({ items, refine, createURL }) {
  console.log(items);
  console.log(createURL);
  return (
    <ul>
      {items.map(item => (
        <li key={item.value}>
          <a
            href={createURL(item.value)}
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
