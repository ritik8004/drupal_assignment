import React from 'react';
import Select from 'react-select';

/**
 * SelectList component.
 *
 * @param {object} props
 *   The props object.
 */
const SelectList = (props) => {
  const {
    options,
    attributeName,
    label,
    defaultValue,
    isHidden,
    allowedValues,
    onChange,
    groupData,
  } = props;

  const { isGroup } = groupData;

  // Event handler for select list value change.
  const onSelect = ({ value, optionLabel }) => onChange(attributeName, value, optionLabel);

  let selectedOption = null;
  const listOptions = options.map((option) => {
    const optionClone = { ...option };
    optionClone.label = isGroup
      ? option.label[groupData.defaultGroup]
      : option.label;

    if (!allowedValues.includes(option.value)
      && !allowedValues.includes(parseInt(option.value, 10))) {
      optionClone.isDisabled = true;
    }
    /* eslint-disable eqeqeq */
    if (option.value == defaultValue) {
      selectedOption = optionClone;
    }
    return optionClone;
  });

  let classes = 'form-select-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;

  return (
    <div className={classes}>
      {isGroup && (
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
      <Select
        classNamePrefix="alshayaSelect"
        className="alshaya-select"
        name={attributeName}
        value={selectedOption}
        defaultValue={selectedOption}
        onChange={onSelect}
        options={listOptions}
        isSearchable={false}
      >
        {listOptions}
      </Select>
      <label>
        {label}
      </label>
    </div>
  );
};

export default SelectList;
