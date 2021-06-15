import React from 'react';
import Swatch from '../swatches';

/**
 * SwatchList component.
 *
 * @param {object} props
 *   The props object.
 */
const SwatchList = (props) => {
  const {
    attributeName,
    swatches,
    onClick,
    label,
    defaultValue,
    activeClass,
    disabledClass,
    isHidden,
    allowedValues,
  } = props;
  let selectedSwatchLabel;
  let classes = 'form-swatch-list-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;
  /* disable eslint to disable eqeqeq rule for Swatch. */
  /* eslint-disable */
  const swatchItems = swatches.map((swatch) => {
    if (defaultValue == swatch.value) {
      selectedSwatchLabel = swatch.label;
    }
    return (
      <Swatch
        attributeName={attributeName}
        data={swatch.data}
        type={swatch.type}
        value={swatch.value}
        label={swatch.label}
        key={swatch.value}
        onClick={onClick}
        isSelected={defaultValue == swatch.value}
        activeClass={activeClass}
        disabledClass={disabledClass}
        allowedValues={allowedValues}
      />
    );
  });
  /* eslint-disable */
  return (
    <div className={classes}>
      <label>
        {`${label} : ${selectedSwatchLabel}`}
      </label>
      <ul className={`swatch-list ${attributeName}`} name={attributeName}>
        {swatchItems}
      </ul>
    </div>
  );
};

export default SwatchList;
