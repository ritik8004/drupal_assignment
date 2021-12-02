import React from 'react';

import PriceElement from '../../../utilities/special-price/PriceElement';

export default class CheckoutVirtualCartItem extends React.Component {
  render() {
    const {
      item: {
        title,
        price,
      },
    } = this.props;
    const senderEmail = 'asdf@gmail.com';
    const senderMessage = 'Happy new year my beast';

    return (
      <div className="product-item">
        <div className="spc-product-image">
          Cart Image
          {/* @todo To update code here once API is available. */}
        </div>
        <div className="spc-product-meta-data">
          <div className="spc-product-title-price">
            <div className="spc-product-title">
              {Drupal.t('Alshaya eGift card', {}, { context: 'egift' })}
            </div>
            <div className="spc-product-price">
              <PriceElement amount={price} />
            </div>
          </div>
          <div className="spc-cart-product-attributes">
            <span className="spc-cart-product-attribute-label">{Drupal.t('Style:', {}, { context: 'egift' })}</span>
            <span className="spc-cart-product-attribute-value">{title}</span>
          </div>
          <div className="spc-cart-product-attribute">
            <span className="spc-cart-product-attribute-label">{Drupal.t('Send to:', {}, { context: 'egift' })}</span>
            <span className="spc-cart-product-attribute-value">{ senderEmail }</span>
          </div>
          <div className="spc-cart-product-attribute">
            <span className="spc-cart-product-attribute-label">{Drupal.t('Message:', {}, { context: 'egift' })}</span>
            <span className="spc-cart-product-attribute-value">{ senderMessage }</span>
          </div>
        </div>
      </div>
    );
  }
}
