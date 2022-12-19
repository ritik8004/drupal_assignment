import React from 'react';
import { hasValue } from '../../../../../../js/utilities/conditionsUtility';

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

  const swatchClassName = (!hasValue(nextValues) || (nextValues.indexOf(attr) !== -1))
    ? 'in-active'
    : 'in-active disabled';

  const values = hasValue(value)
    ? value.split('|')
    : [];
  if (swatchType === 'RGB' && values.length > 1) {
    return (
      <li key={attr} id={`value${attr}`} className={swatchClassName} value={attr} data-attribute-label={label}>
        <a className="dual-color-tone" href="#" onClick={(e) => handleLiClick(e, code)}>
          <div style={{
            backgroundColor: values[0],
          }}
          />
          <div style={{
            backgroundColor: values[1],
          }}
          />
        </a>
      </li>
    );
  }

  const backgroundStyle = (swatchType === 'RGB')
    ? { backgroundColor: value }
    : { backgroundImage: Drupal.url(value) };

  return (
    <li key={attr} id={`value${attr}`} className={swatchClassName} value={attr} data-attribute-label={label}>
      <a href="#" style={backgroundStyle} onClick={(e) => handleLiClick(e, code)} />
    </li>
  );
};

export default AvailableSwatchOptions;
