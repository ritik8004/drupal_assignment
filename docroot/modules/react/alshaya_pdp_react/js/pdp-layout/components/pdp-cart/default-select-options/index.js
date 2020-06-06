import React from 'react';

const DefaultSelectOptions = (props) => {
  const {
    attr,
    groupData,
    value,
  } = props;

  return (
    <option
      value={attr}
      key={attr}
      groupdata={groupData}
    >
      {value}
    </option>
  );
};

export default DefaultSelectOptions;
