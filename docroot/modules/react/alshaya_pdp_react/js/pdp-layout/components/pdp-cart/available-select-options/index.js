import React from 'react';

const AvailableSelectOptions = (props) => {
  const {
    nextValues,
    attr,
    value,
  } = props;

  if (nextValues.indexOf(attr) !== -1) {
    return (
      <option
        value={attr}
        key={attr}
      >
        {value}
      </option>
    );
  }
  return (
    <option
      value={attr}
      key={attr}
      disabled
    >
      {value}
    </option>
  );
};

export default AvailableSelectOptions;
