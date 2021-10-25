import React from 'react';

const AuraRedeemPointsTextField = (props) => {
  const {
    name,
    placeholder,
    onChangeCallback,
    money,
    currencyCode,
    type,
    value,
    disabled,
  } = props;

  if (type === 'money') {
    const { currency_config: currencyConfig } = drupalSettings.alshaya_spc;
    const formattedMoneyValue = money
      ? parseFloat(money).toLocaleString(
        undefined, {
          minimumFractionDigits: 2,
          maximumFractionDigits: currencyConfig.decimal_points,
        },
      )
      : '';

    return (
      <div className={`spc-aura-textfield ${name}-form-item`}>
        <input
          placeholder={placeholder}
          name={name}
          className={name}
          defaultValue={formattedMoneyValue ? `${currencyCode} ${formattedMoneyValue}` : ''}
          type="text"
          disabled
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
        value={value || ''}
        disabled={disabled}
      />
    </div>
  );
};

export default AuraRedeemPointsTextField;
