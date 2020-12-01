import React from 'react';

const AuraRedeemPointsTextField = (props) => {
  const {
    name,
    placeholder,
    onChangeCallback,
    money,
    currencyCode,
    type,
  } = props;

  if (type === 'money') {
    return (
      <div className={`spc-aura-textfield ${name}-form-item`}>
        <input
          placeholder={placeholder}
          name={name}
          className={name}
          defaultValue={money ? `${currencyCode} ${money}` : ''}
          type="text"
        />
      </div>
    );
  }

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
