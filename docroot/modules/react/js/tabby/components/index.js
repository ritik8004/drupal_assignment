import React from 'react';

const TabbyWidget = (props) => {
  const {
    classNames, mobileOnly, pageType, id,
  } = props;

  switch (pageType) {
    case 'pdp':
    case 'cart':
      if (pageType === 'cart' && (mobileOnly && window.innerWidth >= 768)) {
        return null;
      }
      return (
        <div className="tabby">
          <div
            className={`${classNames} ${drupalSettings.tabby.widgetInfo.class}`}
            id={id}
          />
        </div>
      );
    case 'checkout':
      return (
        <button type="button" className={classNames} data-tabby-info="installments" />
      );
    default:
      return null;
  }
};

export default TabbyWidget;
