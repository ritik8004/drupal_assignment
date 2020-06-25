import React from 'react';

const DefaultSelectOptions = (props) => {
  const {
    attr,
    value,
    handleLiClick,
    selected,
    code,
  } = props;

  return (
    <li
      key={attr}
      value={attr}
      className={`magv2-select-list-item ${((selected !== undefined
        && String(selected) === String(attr)))
        ? 'active' : 'in-active'}`}
    >
      <span onClick={(e) => handleLiClick(e, code)} className="magv2-select-list-wrapper">
        {value}
      </span>
    </li>
  );
};

export default DefaultSelectOptions;
