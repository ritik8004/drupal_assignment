import React from 'react';

const SelectSizeButton = (props) => {
  const { label, openModal } = props;

  return (
    <div className="magv2-size-btn-wrapper" onClick={() => openModal()}>{label}</div>
  );
};

export default SelectSizeButton;
