import React from 'react';
import parse from 'html-react-parser';

const OnlineReturnsCartBanner = () => (
  <div className="online-returns-cart-banner">
    <span>{parse(Drupal.t('<b>FREE Returns</b> Now Available Online!', {}, { context: 'online_returns' }))}</span>
  </div>
);

export default OnlineReturnsCartBanner;
