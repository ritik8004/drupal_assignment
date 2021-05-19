import React from 'react';
import Select from 'react-select';

/**
 * Provides the Quantity Dropdown component.
 */
const Dropdown = (props) => {
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
    const optionList = {};

    optionList.value = option;
    optionList.label = option;

    /* eslint-disable eqeqeq */
    if (optionList.value == quantity) {
      selectedValue = optionList;
    }

    return optionList;
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
      />
      <label>
        {label}
      </label>
    </div>
  );
};

export default Dropdown;
