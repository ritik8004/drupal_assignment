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
  onClick(attributeName, e.target.parentElement.dataset.value);
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

  const isColor = type === 'color';

  return (
    <li
      className={isColor ? 'li-swatch-color' : 'li-swatch-image'}
    >
      <span className="swatch-label">{label}</span>
      <ConditionalView condition={isColor}>
        <a
          id={`value${value}`}
          data-value={value}
          href="#"
          style={{ backgroundColor: data }}
          onClick={(e) => onSwatchSelect(e, attributeName, onClick)}
        />
      </ConditionalView>
      <ConditionalView condition={!isColor}>
        <a
          id={`value${value}`}
          data-value={value}
          className={classes}
          href="#"
          onClick={(e) => onSwatchSelect(e, attributeName, onClick)}
        >
          <img src={data} />
        </a>

      </ConditionalView>
    </li>
  );
};

export default Swatch;
