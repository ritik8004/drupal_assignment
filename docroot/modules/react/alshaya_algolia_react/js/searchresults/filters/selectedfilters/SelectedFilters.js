import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import FiltersLabels from './FiltersLabels';

const CurrentFilters = ({ items, refine, createURL }) => {
  return(
    <ul>
      {
        items.map(item => {
          return (
            <React.Fragment>
              {item.items ? (
                <React.Fragment>
                    {
                      item.items.map(nested => {
                        return (
                          <li key={nested.label}>
                            <a
                              href={createURL(nested.value)}
                              onClick={event => {
                                event.preventDefault();
                                refine(nested.value);
                              }}
                            >
                              <FiltersLabels value={nested.label} attribute={item.attribute} />
                            </a>
                          </li>
                        );
                      })
                    }
                </React.Fragment>
              ) : (
                <li>
                  <a
                    href={createURL(item.value)}
                    onClick={event => {
                      event.preventDefault();
                      refine(item.value);
                    }}
                  >
                    <FiltersLabels value={item.label} attribute={item.attribute} />
                  </a>
                </li>
              )}
            </React.Fragment>
          );
        })
      }
    </ul>
  );
};

export default connectCurrentRefinements(CurrentFilters);
