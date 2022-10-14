import React from 'react';

const AvailableSwatchOptions = (props) => {
  const {
    nextValues,
    attr,
    value,
    handleLiClick,
    code,
    label,
    swatchType,
  } = props;

  const backgroundStyle = (swatchType === 'RGB'
    ? `backgroundColor: ${value}` : `backgroundImage: url(${value})`);

  if (nextValues.indexOf(attr) !== -1) {
    return (
      <li key={attr} id={`value${attr}`} className="in-active" value={attr} data-attribute-label={label}>
        <a href="#" style={backgroundStyle} onClick={(e) => handleLiClick(e, code)} />
      </li>
    );
  }
  return (
    <li key={attr} className="in-active disabled" id={`value${attr}`} value={attr} data-attribute-label={label}>
      <a href="#" style={backgroundStyle} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default AvailableSwatchOptions;
