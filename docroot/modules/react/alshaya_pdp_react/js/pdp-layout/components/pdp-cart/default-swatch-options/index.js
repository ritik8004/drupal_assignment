import React from 'react';

const DefaultSwatchOptions = (props) => {
  const {
    attr,
    value,
    handleLiClick,
    code,
  } = props;

  return (
    <li key={attr} id={`value${attr}`} className="in-active" value={attr}>
      <a href="#" style={{ backgroundImage: `url(${value})` }} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default DefaultSwatchOptions;
