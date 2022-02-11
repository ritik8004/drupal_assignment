import React from 'react';
import Collapsible from 'react-collapsible';

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
    index,
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
      && !allowedValues.includes(option.value_id)
      && !allowedValues.includes(parseInt(option.value_id, 10))) {
      classes.push(disabledClass);
      return null;
    }

    /* eslint-disable eqeqeq */
    if (option.value_id == defaultValue) {
      classes.push(activeClass);
      selectedValueLabel = isGroup
        ? `${groupData.defaultGroup}, ${optionLabel}`
        : optionLabel;
      element = (
        <li
          key={option.value_id}
          value={option.value_id}
          className={classes.join(' ')}
          selected="selected"
          onClick={(e) => onClick(attributeName, e.target.value)}
        >
          {optionLabel}
        </li>
      );
    } else {
      element = (
        <li
          key={option.value_id}
          value={option.value_id}
          onClick={(e) => onClick(attributeName, e.target.value)}
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

  const SofaSectionConfigItemAccordion = (
    <label className={selectedValueLabel ? 'active' : ''}>
      <div className="config-number-wrapper">
        <span className="config-index-number">
          {index}
        </span>
      </div>
      <div className="config-text-wrapper">
        <span className="config-name">
          {Drupal.t('select')}
          {' '}
          {label}
        </span>
        <span className="config-value">
          {selectedValueLabel}
        </span>
      </div>
    </label>
  );

  return (
    <div className={classes}>
      <Collapsible trigger={SofaSectionConfigItemAccordion} open="true">
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
      </Collapsible>
    </div>
  );
};

export default UnorderedList;
