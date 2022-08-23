import React from 'react';

const OnlineReturnsCartBanner = () => {
  // Extract the cart banner status.
  const { cartBanner } = drupalSettings.onlineReturns;

  if (cartBanner) {
    return (
      <div>
        <span><b>{Drupal.t('FREE Returns', {}, { context: 'online_returns' })}</b></span>
        <span>
          {Drupal.t('Now Available Online!', {}, { context: 'online_returns' })}
        </span>
      </div>
    );
  }

  return '';
};

export default OnlineReturnsCartBanner;
