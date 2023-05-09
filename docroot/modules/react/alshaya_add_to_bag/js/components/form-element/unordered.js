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
    groupData,
  } = props;
  let selectedValueLabel = null;
  const { isGroup } = groupData;
  const listItems = options.map((option) => {
    let element = null;
    const classes = [];
    const optionLabel = isGroup
      ? option.label[groupData.defaultGroup]
      : option.label;

    // Get selected size label for GTM size click event.
    // Set size in '{size_attribute_name}: {size_value}'
    // format eg for VS brand, 'band_size: 32', 'cup_size: D'.
    // If size goup is available we need to push the size
    // group also eg for FL brand 'size_shoe_eu: UK, 8.5'.
    const selectedOptionLabel = isGroup
      ? `${attributeName}: ${groupData.defaultGroup}, ${optionLabel}`
      : `${attributeName}: ${optionLabel}`;

    if (allowedValues.length > 0
      && !allowedValues.includes(option.value)
      && !allowedValues.includes(parseInt(option.value, 10))) {
      classes.push(disabledClass);
    }

    /* eslint-disable eqeqeq */
    if (option.value == defaultValue) {
      classes.push(activeClass);
      selectedValueLabel = isGroup
        ? `${groupData.defaultGroup}, ${optionLabel}`
        : optionLabel;
      element = (
        <li
          key={option.value}
          value={option.value}
          className={classes.join(' ')}
          selected="selected"
          onClick={(e) => onClick(attributeName, e.target.value, selectedOptionLabel)}
        >
          {optionLabel}
        </li>
      );
    } else {
      element = (
        <li
          key={option.value}
          value={option.value}
          onClick={(e) => onClick(attributeName, e.target.value, selectedOptionLabel)}
          className={classes.join(' ')}
        >
          {optionLabel}
        </li>
      );
    }

    return element;
  });

  let classes = 'form-list-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;
  classes = isGroup ? `${classes} group-wrapper` : `${classes}`;

  return (
    <div className={classes}>
      <label>
        <span>
          {`${label}: `}
        </span>
        <span className="selected-text">
          {selectedValueLabel}
        </span>
      </label>
      { isGroup && (
        <div className="group-anchor-wrapper">
          {Object.keys(groupData.groupAlternates).map((alternate) => (
            <a
              href="#"
              key={alternate}
              onClick={(e) => groupData.setGroupCode(e, alternate)}
              className={((groupData.defaultGroup === groupData.groupAlternates[alternate]))
                ? 'active' : 'in-active'}
            >
              {groupData.groupAlternates[alternate]}
            </a>
          ))}
        </div>
      )}
      <ul className={`attribute-options-list ${attributeName}`} name={attributeName}>{listItems}</ul>
    </div>
  );
};

export default UnorderedList;
