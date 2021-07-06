import React, { useEffect } from 'react';

const PostpayCart = (props) => {
  const {
    amount, classNames, mobileOnly, pageType,
  } = props;

  useEffect(() => {
    if (pageType === 'pdp') {
      window.postpay.ui.refresh();
    }
  });

  if ((pageType === 'cart' && !(mobileOnly && window.innerWidth >= 768))
    || pageType === 'pdp') {
    return (
      <div className={`postpay ${drupalSettings.postpay_widget_info.postpay_mode_class}`}>
        <div
          className={`${classNames} ${drupalSettings.postpay_widget_info.class}`}
          data-type={drupalSettings.postpay_widget_info['data-type']}
          data-amount={(amount * drupalSettings.postpay.currency_multiplier).toFixed(0)}
          data-currency={drupalSettings.postpay_widget_info['data-currency']}
          data-num-instalments={drupalSettings.postpay_widget_info['data-num-instalments']}
          data-locale={drupalSettings.postpay_widget_info['data-locale']}
        />
      </div>
    );
  }
  return (null);
};

export default PostpayCart;
