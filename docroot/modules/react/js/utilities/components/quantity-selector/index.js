import React from 'react';
import Select from 'react-select';

/**
 * Provides the Quantity Dropdown component.
 */
const QuantitySelector = (props) => {
  const {
    options, onChange, quantity, label,
  } = props;
  // Do not process further if there are no options.
  if (options.length === 0) {
    return (null);
  }

  let selectedValue = null;

  const handleChange = (selectedOption) => {
    onChange(selectedOption.value);
  };

  const quantityOptions = options.map((option) => {
    /* eslint-disable eqeqeq */
    if (option.value == quantity) {
      selectedValue = option;
    }

    return option;
  });


  return (
    <div className="alshaya-select-wrapper">
      <Select
        classNamePrefix="alshayaSelect"
        className="alshaya-select"
        onChange={handleChange}
        options={quantityOptions}
        value={selectedValue}
        defaultValue={selectedValue}
        isSearchable={false}
        isOptionDisabled={(option) => option.disabled}
      />
      <label>
        {label}
      </label>
    </div>
  );
};

export default QuantitySelector;
