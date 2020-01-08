import React, { useRef }  from 'react';

const SelectedFilters = (props) => {
  const selectedFilterRef = useRef();

  const filtersCallBack = (itemCount) => {
    if (typeof selectedFilterRef.current === 'object' && selectedFilterRef.current !== null) {
      // Hide selected filters div when theere are not selected filters.
      selectedFilterRef.current.style.display = (!itemCount) ? 'none' : 'block';
    }
  }

  return (
    <div id="block-filterbar" className="block block-facets-summary block-facets-summary-blockfilter-bar" ref={selectedFilterRef} style={{display: 'none'}}>
      <span className="filter-list-label">{Drupal.t('selected filters')}</span>
      {props.children(filtersCallBack)}
    </div>
  );
}

export default SelectedFilters;
