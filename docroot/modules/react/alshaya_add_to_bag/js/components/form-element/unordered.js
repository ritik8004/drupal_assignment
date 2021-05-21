import React from 'react';

/**
 * Unorderedlist component.
 *
 * @param {object} props
 *   The props object.
 */
const UnorderedList = (props) => {
  const {
    options,
    attributeName,
    label,
    defaultValue,
    activeClass,
    disabledClass,
    isHidden,
    allowedValues,
    onClick,
  } = props;
  let selectedValueLabel = null;
  const listItems = options.map((option) => {
    let element = null;
    const classes = [];
    if (!allowedValues.includes(option.value)
      && !allowedValues.includes(parseInt(option.value, 10))) {
      classes.push(disabledClass);
    }

    /* eslint-disable eqeqeq */
    if (option.value == defaultValue) {
      classes.push(activeClass);
      selectedValueLabel = option.label;
      element = (
        <li
          key={option.value}
          value={option.value}
          className={classes.join(' ')}
          selected="selected"
          onClick={(e) => onClick(attributeName, e.target.value)}
        >
          {option.label}
        </li>
      );
    } else {
      element = (
        <li
          key={option.value}
          value={option.value}
          onClick={(e) => onClick(attributeName, e.target.value)}
          className={classes.join(' ')}
        >
          {option.label}
        </li>
      );
    }

    return element;
  });

  let classes = 'form-list-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;

  return (
    <div className={classes}>
      <label>
        {`${label} : ${selectedValueLabel}`}
      </label>
      <ul className={`attribute-options-list ${attributeName}`} name={attributeName}>{listItems}</ul>
    </div>
  );
};

export default UnorderedList;
