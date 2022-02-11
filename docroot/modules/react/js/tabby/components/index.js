import React from 'react';

const TabbyWidget = (props) => {
  const {
    classNames, mobileOnly, pageType, id,
  } = props;
  const { tabby: tabbyConfig, path } = window.drupalSettings;

  switch (pageType) {
    case 'pdp':
    case 'cart':
      if (pageType === 'cart' && (mobileOnly && window.innerWidth >= 768)) {
        return null;
      }
      return (
        <div className="tabby">
          <div
            className={`${classNames} ${tabbyConfig.widgetInfo.class}`}
            id={id}
          />
        </div>
      );
    case 'checkout':
      return (
        <button type="button" className={classNames} data-tabby-info-alshaya="installments" data-tabby-language={path.currentLanguage} />
      );
    default:
      return null;
  }
};

export default TabbyWidget;
