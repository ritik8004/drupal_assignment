import React from 'react';

const TabbyCart = (props) => {
  const {
    pageType,
  } = props;

  if (pageType === 'pdp') {
    return (
      <div className="tabby">
        <div id={drupalSettings.tabby.selector} />
      </div>
    );
  }
  return (null);
};

export default TabbyCart;
