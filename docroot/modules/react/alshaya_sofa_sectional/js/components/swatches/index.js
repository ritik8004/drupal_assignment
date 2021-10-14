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
  const swatchValue = e.currentTarget.firstChild.dataset.value;
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

  if (isSelected) {
    classes.push(activeClass);
  }
  if ((allowedValues.length > 0) && !allowedValues.includes(value)) {
    classes.push(disabledClass);
  }

  const isColor = (type === 'color' || type === 'text');

  return (
    <li
      className={isColor ? `li-swatch-color ${classes}` : `li-swatch-image ${classes}`}
      onClick={(e) => onSwatchSelect(e, attributeName, onClick)}
    >
      <ConditionalView condition={isColor}>
        <a
          id={`value${value}`}
          data-value={value}
          href="#"
          style={{ backgroundColor: data }}
        />
      </ConditionalView>
      <ConditionalView condition={!isColor}>
        <a
          id={`value${value}`}
          data-value={value}
          href="#"
        >
          <img loading="lazy" src={data} />
        </a>
      </ConditionalView>
      <span className="swatch-color">{label}</span>
    </li>
  );
};

export default Swatch;
