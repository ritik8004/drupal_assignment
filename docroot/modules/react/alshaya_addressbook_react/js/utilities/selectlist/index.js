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
    onChange,
  } = props;

  // Event handler for select list value change.
  const onSelect = ({ value }) => onChange(attributeName, value);

  let selectedOption = null;
  const listOptions = options.map((option) => {
    const optionClone = { ...option };

    /* eslint-disable eqeqeq */
    if (option.value == defaultValue) {
      selectedOption = optionClone;
    }
    return optionClone;
  });

  let classes = 'form-select-wrapper form-type-select form-item';
  classes = isHidden ? `${classes} form-element-hidden` : `${classes}`;

  return (
    <div className={classes}>
      <label>
        {label}
      </label>
      <Select
        className="alshaya-select"
        name={attributeName}
        value={selectedOption}
        defaultValue={selectedOption}
        onChange={onSelect}
        options={listOptions}
        isSearchable
      >
        {listOptions}
      </Select>
      <div id={`${attributeName}-error`} className="error" />
    </div>
  );
};

export default SelectList;
