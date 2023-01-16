import React from 'react';

/**
 * Click handler for the swatch item.
 *
 * @param {object} e
 *   The event object.
 */
const onSwatchSelect = (e, attributeName, swatchLabel, onClick) => {
  e.preventDefault();
  // Get value from current element if its not image swatch or dual tone color swatch,
  // else get value from parent element.
  const swatchValue = (e.target.nodeName.toLowerCase() === 'a')
    ? e.target.dataset.value
    : e.target.parentElement.dataset.value;
  onClick(attributeName, swatchValue, swatchLabel);
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
        <ColorSwatch
          data={data}
          value={value}
          label={label}
          classes={classes}
          onClick={onClick}
          attributeName={attributeName}
        />
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
            onClick={(e) => onSwatchSelect(e, attributeName, label, onClick)}
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
            onClick={(e) => onSwatchSelect(e, attributeName, label, onClick)}
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

const ColorSwatch = ({
  data,
  value,
  label,
  classes,
  onClick,
  attributeName,
}) => {
  const values = data.split('|');
  if (values.length > 1) {
    return (
      <li className="li-swatch-color dual-color-tone" key={value}>
        <span className="swatch-label">{label}</span>
        <a
          id={`value${value}`}
          data-value={value}
          className={classes}
          href="#"
          onClick={(e) => onSwatchSelect(e, attributeName, label, onClick)}
        >
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
  return (
    <li className="li-swatch-color dual-color-tone" key={value}>
      <span className="swatch-label">{label}</span>
      <a
        id={`value${value}`}
        data-value={value}
        className={classes}
        href="#"
        style={{ backgroundColor: data }}
        onClick={(e) => onSwatchSelect(e, attributeName, label, onClick)}
      />
    </li>
  );
};

export default Swatch;
