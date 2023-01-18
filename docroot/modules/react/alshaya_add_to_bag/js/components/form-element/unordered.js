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
          onClick={(e) => onClick(attributeName, e.target.value, optionLabel)}
        >
          {optionLabel}
        </li>
      );
    } else {
      element = (
        <li
          key={option.value}
          value={option.value}
          onClick={(e) => onClick(attributeName, e.target.value, optionLabel)}
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
