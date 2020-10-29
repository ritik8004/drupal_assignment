import React from 'react';
import GridButtons from '../grid-buttons';

export default function GridAndCount(props) {
  const { children } = props;
  return (
    <div className="block block-alshaya-grid-count-block">
      <div className="total-result-count">
        <div className="view-header search-count tablet">
          {children}
        </div>
      </div>
      <GridButtons />
    </div>
  );
}
