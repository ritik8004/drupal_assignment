import React from 'react'
import GridButtons from '../components/grid-buttons';

export default function GridAndCount(props) {
  return (
    <div className="block block-alshaya-search-api block-alshaya-grid-count-block">
      <div className="total-result-count">
        <div className="view-header search-count tablet">
          {props.children}
        </div>
      </div>
      <GridButtons />
    </div>
  );
};

