import React, { useRef, useEffect }  from 'react';
import { connectCurrentRefinements } from 'react-instantsearch-dom';
import FiltersLabels from './FiltersLabels';
import ClearRefinements from './ClearFilters';
const _ = require("lodash");

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
        (items.length > 0) ? <li className="clear-all"><ClearRefinements /></li> : ''
      }
    </ul>
  );
});

const SelectedFilters = (props) => {
  const selectedFilterRef = useRef();

  useEffect(() => {
    setTimeout(() => {
      // Hide selected filters div when theere are not selected filters.
      const selectedFilters = selectedFilterRef.current;
      if (selectedFilters.querySelector('li') === null) {
        selectedFilters.style.display = 'none';
      }
      else {
        selectedFilters.style.display = 'block';
      }

    }, 500);
  });

  return (
    <div id="block-filterbar" className="block block-facets-summary block-facets-summary-blockfilter-bar" ref={selectedFilterRef} style={{display: 'none'}}>
      <span className="filter-list-label">Selected Filters</span>
      <CustomCurrentFilters />
    </div>
  );
}

export default SelectedFilters;
