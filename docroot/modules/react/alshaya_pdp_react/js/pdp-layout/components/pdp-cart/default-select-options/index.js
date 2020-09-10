import React from 'react';

const DefaultSelectOptions = ({
  attr, value, handleLiClick, selected, code,
}) => (
  <li
    key={attr}
    value={attr}
    id={`value${attr}`}
    className={`magv2-select-list-item ${((selected !== undefined
        && String(selected) === String(attr)))
      ? 'active' : 'in-active'}`}
    data-attribute-label={value}
  >
    <span onClick={(e) => handleLiClick(e, code)} className="magv2-select-list-wrapper">
      <span className="magv2-select-list-name">{value}</span>
    </span>
  </li>
);

export default DefaultSelectOptions;
