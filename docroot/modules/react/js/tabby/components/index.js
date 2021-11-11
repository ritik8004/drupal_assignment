import React from 'react';

const TabbyWidget = (props) => {
  const {
    amount, classNames, mobileOnly, pageType,
  } = props;

  if ((pageType === 'cart' && !(mobileOnly && window.innerWidth >= 768))
    || pageType === 'pdp') {
    return (
      <div className="tabby">
        <div
          className={`${classNames} ${drupalSettings.tabby_widget_info.class}`}
          data-amount={amount}
          id={drupalSettings.tabby.selector}
        />
      </div>
    );
  }
  return null;
};

export default TabbyWidget;
