import React from 'react';

const AvailableSwatchOptions = (props) => {
  const {
    nextValues,
    attr,
    value,
    handleLiClick,
    code,
  } = props;

  if (nextValues.indexOf(attr) !== -1) {
    return (
      <li key={attr} id={`value${attr}`} className="in-active" value={attr}>
        <a href="#" style={{ backgroundImage: `url(${value})` }} onClick={(e) => handleLiClick(e, code)} />
      </li>
    );
  }
  return (
    <li key={attr} className="in-active disabled" id={`value${attr}`} value={attr}>
      <a href="#" style={{ backgroundImage: `url(${value})` }} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default AvailableSwatchOptions;
