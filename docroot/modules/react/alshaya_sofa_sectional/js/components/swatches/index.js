import React from 'react';
import ConditionalView from '../../../../js/utilities/components/conditional-view';

/**
 * Click handler for the swatch item.
 *
 * @param {object} e
 *   The event object.
 */
const onSwatchSelect = (e, attributeName, onClick) => {
  e.preventDefault();
  const swatchValue = e.currentTarget.dataset.value;
  onClick(attributeName, swatchValue);
};

const Swatch = (props) => {
  const {
    data,
    type,
    value,
    label,
    isSelected,
    activeClass,
    disabledClass,
    allowedValues,
    onClick,
    attributeName,
  } = props;

  const classes = [];
  let optionalLabel = label;
  let optionalFabricLabel = null;

  if (isSelected) {
    classes.push(activeClass);
  }

  if ((allowedValues.length > 0) && !allowedValues.includes(value.toString())) {
    classes.push(disabledClass);
    return null;
  }

  const isColor = (type === 'color' || type === 'text');

  if (attributeName === 'fabric_color') {
    const labelParts = label.split('-');
    if (labelParts.length > 1) {
      optionalLabel = labelParts['0'];
      optionalFabricLabel = labelParts['1'];
    }
  }

  return (
    <li
      className={isColor ? `li-swatch-color ${classes}` : `li-swatch-image ${classes}`}
      data-value={value}
      onClick={(e) => onSwatchSelect(e, attributeName, onClick)}
    >
      <ConditionalView condition={(isColor) && (data !== null)}>
        <a
          id={`value${value}`}
          href="#"
          style={{ backgroundColor: data }}
        />
      </ConditionalView>
      <ConditionalView condition={(!isColor) && (data !== null)}>
        <a
          id={`value${value}`}
          href="#"
        >
          <img loading="lazy" src={data} />
        </a>
      </ConditionalView>
      <span className="swatch-color">{optionalLabel}</span>
      <ConditionalView condition={optionalFabricLabel !== null}>
        <span className="swatch-fabric">{optionalFabricLabel}</span>
      </ConditionalView>
    </li>
  );
};

export default Swatch;
