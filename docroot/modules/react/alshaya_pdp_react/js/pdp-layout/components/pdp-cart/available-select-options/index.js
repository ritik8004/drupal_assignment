import React from 'react';

const AvailableSelectOptions = ({
  nextValues, attr, value, handleLiClick, selected, code,
}) => {
  if (nextValues.indexOf(attr) !== -1) {
    return (
      <li
        key={attr}
        value={attr}
        id={`value${attr}`}
        className={`magv2-select-list-item ${((selected !== undefined
          && String(selected) === String(attr)))
          ? 'active' : 'in-active'}`}
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
      className={`magv2-select-list-item disabled ${((selected !== undefined
        && String(selected) === String(attr)))
        ? 'active' : 'in-active'}`}
    >
      <span onClick={(e) => handleLiClick(e, code)} className="magv2-select-list-wrapper">
        <span className="magv2-select-list-name">{value}</span>
      </span>
    </li>
  );
};

export default AvailableSelectOptions;
