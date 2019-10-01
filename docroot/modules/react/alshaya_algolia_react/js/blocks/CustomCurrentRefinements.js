import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';

const CurrentRefinements = ({ items, refine, createURL }) => {
  return(
    <ul>
      {items.map(item => (
        <li key={item.label}>
          {item.items ? (
            <React.Fragment>
              {item.label}
              <ul>
                {item.items.map(nested => (
                  <li key={nested.label}>
                    <a
                      href={createURL(nested.value)}
                      onClick={event => {
                        event.preventDefault();
                        refine(nested.value);
                      }}
                    >
                      {nested.label}
                    </a>
                  </li>
                ))}
              </ul>
            </React.Fragment>
          ) : (
            <a
              href={createURL(item.value)}
              onClick={event => {
                event.preventDefault();
                refine(item.value);
              }}
            >
              {item.label}
            </a>
          )}
        </li>
      ))}
    </ul>
  );
};

export default connectCurrentRefinements(CurrentRefinements);
