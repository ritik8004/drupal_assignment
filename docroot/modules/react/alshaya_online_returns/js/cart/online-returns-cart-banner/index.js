import React from 'react';
import parse from 'html-react-parser';

const OnlineReturnsCartBanner = () => {
  // Extract the cart banner status.
  const { cartBanner } = drupalSettings.onlineReturns;

  if (cartBanner) {
    return (
      <div className="online-returns-cart-banner">
        <span>{ parse(Drupal.t('<b>FREE Returns</b> Now Available Online!', {}, { context: 'online_returns' })) }</span>
      </div>
    );
  }

  return '';
};

export default OnlineReturnsCartBanner;
