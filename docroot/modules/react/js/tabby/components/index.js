import React from 'react';
import { isMobile } from '../../utilities/display';

const TabbyWidget = (props) => {
  const {
    classNames, mobileOnly, pageType, id, amount,
  } = props;

  const {
    tabby: tabbyConfig,
    path,
    alshaya_spc: spcConfig,
  } = window.drupalSettings;

  switch (pageType) {
    case 'pdp':
    case 'cart':
      if (pageType === 'cart' && ((mobileOnly === true && !isMobile()) || (mobileOnly === false && isMobile()))) {
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
        <button
          type="button"
          className={classNames}
          data-tabby-info-alshaya="installments"
          data-tabby-language={path.currentLanguage}
          data-tabby-price={amount}
          data-tabby-currency={spcConfig.currency_config.currency_code}
        />
      );
    default:
      return null;
  }
};

export default TabbyWidget;
