import React from 'react';

const PostpayCart = ({ amount, isCartPage }) => {
  if (typeof drupalSettings.postpay_widget_info !== 'undefined' && isCartPage !== false) {
    return (
      <span>
        <div
          className={`spc-postpay ${drupalSettings.postpay_widget_info.class}`}
          data-type={drupalSettings.postpay_widget_info['data-type']}
          data-amount={amount * drupalSettings.postpay.currency_multiplier}
          data-currency={drupalSettings.postpay_widget_info['data-currency']}
          data-num-instalments={drupalSettings.postpay_widget_info['data-num-instalments']}
          data-locale={drupalSettings.postpay_widget_info['data-locale']}
        />
      </span>
    );
  }
  return (null);
};

export default PostpayCart;
