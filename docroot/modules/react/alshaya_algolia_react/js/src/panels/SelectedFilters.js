import React, { useRef, useEffect }  from 'react';
import { updateAfter } from '../utils';

const SelectedFilters = (props) => {
  const selectedFilterRef = useRef();

  useEffect(() => {
    setTimeout(() => {
      if (typeof selectedFilterRef.current === 'object' && selectedFilterRef.current !== null) {
        // Hide selected filters div when theere are not selected filters.
        const selectedFilters = selectedFilterRef.current;

        if (selectedFilters.querySelector('li') === null) {
          selectedFilters.style.display = 'none';
        }
        else {
          selectedFilters.style.display = 'block';
        }
      }
    }, updateAfter);
  });

  return (
    <div id="block-filterbar" className="block block-facets-summary block-facets-summary-blockfilter-bar" ref={selectedFilterRef} style={{display: 'none'}}>
      <span className="filter-list-label">{Drupal.t('selected filters')}</span>
      {props.children}
    </div>
  );
}

export default SelectedFilters;
