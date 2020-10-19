import React from 'react';

const AuraRedeemPointsTextField = (props) => {
  const { name, placeholder, onChangeCallback } = props;

  return (
    <div className={`spc-aura-textfield ${name}-form-item`}>
      <input
        placeholder={placeholder}
        name={name}
        className={name}
        type="text"
        onChange={(e) => onChangeCallback(e)}
      />
    </div>
  );
};

export default AuraRedeemPointsTextField;
