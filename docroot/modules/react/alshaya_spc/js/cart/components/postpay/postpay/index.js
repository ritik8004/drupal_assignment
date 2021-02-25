import React from 'react';

const PostpayCart = (props) => {
  const {
    amount, isCartPage, classNames, mobileOnly,
  } = props;
  if (isCartPage === true
    && !(mobileOnly && window.innerWidth >= 768)) {
    return (
      <div
        className={`${classNames} ${drupalSettings.postpay_widget_info.class}`}
        data-type={drupalSettings.postpay_widget_info['data-type']}
        data-amount={amount * drupalSettings.postpay.currency_multiplier}
        data-currency={drupalSettings.postpay_widget_info['data-currency']}
        data-num-instalments={drupalSettings.postpay_widget_info['data-num-instalments']}
        data-locale={drupalSettings.postpay_widget_info['data-locale']}
      />
    );
  }
  return (null);
};

export default PostpayCart;
