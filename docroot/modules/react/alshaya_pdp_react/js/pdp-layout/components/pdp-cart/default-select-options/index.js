import React from 'react';

const DefaultSelectOptions = (props) => {
  const {
    attr,
    value,
  } = props;

  return (
    <option
      value={attr}
      key={attr}
    >
      {value}
    </option>
  );
};

export default DefaultSelectOptions;
