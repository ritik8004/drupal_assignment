import React from 'react';

import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import { isMobile } from '../../../../../js/utilities/display';

const {
  defaultColgrid: defaultColGridDesktop,
  defaultColGridMobile,
} = drupalSettings.algoliaSearch;

let defaultcolgrid = isMobile() ? defaultColGridMobile : defaultColGridDesktop;
// Set default value for col grid.
if (!hasValue(defaultcolgrid)) {
  defaultcolgrid = 'small';
}

const GridButtons = ({
  toggle,
}) => (
  <div className="grid-buttons">
    <div className={`large-col-grid ${defaultcolgrid === 'large' ? 'active' : ''}`} onClick={toggle}>
      <svg className="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z" />
        </g>
      </svg>
      <svg className="g1" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <path className="grid" fill="#DADADA" fillRule="nonzero" d="M0 0h15v15H0z" />
      </svg>
    </div>
    <div className={`small-col-grid ${defaultcolgrid === 'small' ? 'active' : ''}`} onClick={toggle}>
      <svg className="g3" xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h4v7H0zM5 0h4v7H5zM10 0h4v7h-4zM10 8h4v7h-4zM5 8h4v7H5zM0 8h4v7H0z" />
        </g>
      </svg>
      <svg className="g2" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
        <g className="grid" fill="#DADADA" fillRule="nonzero">
          <path d="M0 0h7v15H0zM8 0h7v15H8z" />
        </g>
      </svg>
    </div>
  </div>
);

export default GridButtons;
