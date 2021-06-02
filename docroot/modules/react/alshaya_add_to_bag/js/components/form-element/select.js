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
  } = props;

  // Event handler for select list value change.
  const onSelect = ({ value }) => onChange(attributeName, value);

  let selectedOption = null;
  const listOptions = options.map((option) => {
    const optionClone = { ...option };
    if (!allowedValues.includes(option.value)
      && !allowedValues.includes(parseInt(option.value, 10))) {
      optionClone.isDisabled = true;
    }
    /* eslint-disable eqeqeq */
    if (option.value == defaultValue) {
      selectedOption = option;
    }
    return optionClone;
  });

  let classes = 'form-select-wrapper';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;

  return (
    <div className={classes}>
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
