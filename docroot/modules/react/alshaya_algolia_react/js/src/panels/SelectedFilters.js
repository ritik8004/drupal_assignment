import React, { useRef, useEffect }  from 'react';

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
      {props.children}
    </div>
  );
}

export default SelectedFilters;
