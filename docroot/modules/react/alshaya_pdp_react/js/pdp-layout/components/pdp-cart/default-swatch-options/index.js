import React from 'react';

const DefaultSwatchOptions = (props) => {
  const {
    attr,
    value,
    handleLiClick,
    code,
    label,
  } = props;

  return (
    <li key={attr} id={`value${attr}`} className="in-active" value={attr} data-attribute-label={label}>
      <a href="#" style={{ backgroundImage: `url(${value})` }} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default DefaultSwatchOptions;
