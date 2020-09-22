import React from 'react';

const AvailableSelectOptions = ({
  nextValues, attr, value, handleLiClick, selected, code,
}) => {
  if (nextValues.indexOf(attr) !== -1) {
    let selectedVal = selected;
    // If the previously selected value is disabled.
    if (nextValues.indexOf(String(selectedVal)) === -1) {
      // set first available value as active.
      [selectedVal] = nextValues;
    }
    return (
      <li
        key={attr}
        value={attr}
        id={`value${attr}`}
        className={`magv2-select-list-item ${((selectedVal !== undefined
          && String(selectedVal) === String(attr)))
          ? 'active' : 'in-active'}`}
        data-attribute-label={value}
      >
        <span onClick={(e) => handleLiClick(e, code)} className="magv2-select-list-wrapper">
          <span className="magv2-select-list-name">{value}</span>
        </span>
      </li>
    );
  }
  return (
    <li
      key={attr}
      value={attr}
      id={`value${attr}`}
      className="magv2-select-list-item disabled in-active"
    >
      <span onClick={(e) => handleLiClick(e, code)} className="magv2-select-list-wrapper">
        <span className="magv2-select-list-name">{value}</span>
      </span>
    </li>
  );
};

export default AvailableSelectOptions;
