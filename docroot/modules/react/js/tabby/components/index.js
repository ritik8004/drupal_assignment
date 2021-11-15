import React from 'react';

const TabbyWidget = (props) => {
  const {
    classNames, mobileOnly, pageType, id,
  } = props;

  if ((pageType === 'cart' && !(mobileOnly && window.innerWidth >= 768))
    || pageType === 'pdp') {
    return (
      <div className="tabby">
        <div
          className={`${classNames} ${drupalSettings.tabby.widgetInfo.class}`}
          id={id}
        />
      </div>
    );
  }
  return null;
};

export default TabbyWidget;
