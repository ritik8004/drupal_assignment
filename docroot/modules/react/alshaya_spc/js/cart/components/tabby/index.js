import React from 'react';

const TabbyWidget = (props) => {
  const {
    pageType,
  } = props;

  if (pageType === 'pdp') {
    return (
      <div className="tabby">
        <div className="tabby-widget" id={drupalSettings.tabby.selector} />
      </div>
    );
  }
  return null;
};

export default TabbyWidget;
