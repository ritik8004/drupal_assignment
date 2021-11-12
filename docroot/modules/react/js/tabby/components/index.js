import React from 'react';

const TabbyWidget = (props) => {
  const {
    amount, classNames, pageType,
  } = props;

  if (pageType === 'pdp') {
    return (
      <div className="tabby">
        <div
          className={`${classNames} ${drupalSettings.tabby_widget_info.class}`}
          data-amount={amount}
          id={drupalSettings.tabby.selector} // @todo: Change this to unique for new pdp.
        />
      </div>
    );
  }
  return null;
};

export default TabbyWidget;
