import React from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import FiltersLabels from './FiltersLabels';
import ClearRefinements from '../ClearFilters';
const _ = require("lodash");

export default connectCurrentRefinements(({ items, refine, ...props }) => {
  const uniqueItems = _.uniqBy(items, 'attribute');
  props.callback(uniqueItems.length);

  return(
    <ul>
      {
        uniqueItems.map(item => {
          return (
            <React.Fragment key={item.id}>
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
        (items.length > 0) ? <li className="clear-all"><ClearRefinements title={Drupal.t('clear filters')}/></li> : ''
      }
    </ul>
  );
});
