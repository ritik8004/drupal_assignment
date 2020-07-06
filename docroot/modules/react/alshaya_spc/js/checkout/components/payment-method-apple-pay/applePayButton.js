import React from 'react';

const ApplePayButton = (props) => {
  const {
    placeOrder,
    lang,
    text,
    isaActive,
  } = props;
  const disabled = isaActive !== 'active';

  return (
    <button
      id="ckoApplePayButton"
      type="button"
      onClick={(e) => placeOrder(e)}
      lang={lang}
      disabled={disabled}
      className="apple-pay-button apple-pay-button-with-text apple-pay-button-black-with-text"
    >
      <span className="text">{text}</span>
      <span className="logo" />
    </button>
  );
};

export default ApplePayButton;
