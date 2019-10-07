import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import FiltersLabels from './FiltersLabels';
const _ = require("lodash");


const CustomClearRefinements = connectCurrentRefinements(({ items, refine }) => (
  <a
    href="#"
    onClick={event => {
      event.preventDefault();
      refine(items)
    }}
  >
    Clear Filters
  </a>
));

const CustomCurrentFilters =  connectCurrentRefinements(({ items, refine }) => {
 const uniqueItems = _.uniqBy(items, 'attribute');
  return(
    <ul>
      {
        uniqueItems.map(item => {
          return (
            <React.Fragment>
              {item.items ? (
                <React.Fragment>
                    {
                      item.items.map(nested => {
                        return (
                          <li key={nested.label}>
                            <a
                              href="#"
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
                    href="#"
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
      {
        (items.length > 0) ? <li className="clear-all"><CustomClearRefinements /></li> : ''
      }
    </ul>
  );
};

export default connectCurrentRefinements(CurrentFilters);
