import React from 'react';

/**
 * Click handler for the swatch item.
 *
 * @param {object} e
 *   The event object.
 */
const onSwatchSelect = (e, attributeName, onClick, type) => {
  e.preventDefault();
  const swatchValue = (type !== 'image')
    ? e.target.dataset.value
    : e.target.parentElement.dataset.value;
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

  // Render the LI based on the swatch type.
  switch (type) {
    // If swatch is a type of color.
    case 'color':
      return (
        <li className="li-swatch-color" key={value}>
          <span className="swatch-label">{label}</span>
          <a
            id={`value${value}`}
            data-value={value}
            className={classes}
            href="#"
            style={{ backgroundColor: data }}
            onClick={(e) => onSwatchSelect(e, attributeName, onClick, type)}
          />
        </li>
      );

    // If swatch is a type of text.
    case 'text':
      return (
        <li className="li-swatch-text" key={value}>
          <a
            id={`value${value}`}
            data-value={value}
            className={classes}
            href="#"
            onClick={(e) => onSwatchSelect(e, attributeName, onClick, type)}
          >
            {label}
          </a>
        </li>
      );

    // If swatch is a type of an image.
    case 'image':
      return (
        <li className="li-swatch-image" key={value}>
          <a
            id={`value${value}`}
            data-value={value}
            className={classes}
            href="#"
            onClick={(e) => onSwatchSelect(e, attributeName, onClick, type)}
          >
            <img loading="lazy" src={data} />
          </a>
        </li>
      );

    // By detault return null.
    default:
      return null;
  }
};

export default Swatch;
