import React from 'react';

import PriceElement from '../../../utilities/special-price/PriceElement';
import CheckoutItemImage from '../../../utilities/checkout-item-image';
import ConditionalView from '../../../common/components/conditional-view';
import { getCardNumberForTopUpItem } from '../../../utilities/egift_util';

class CheckoutVirtualCartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const {
      item: {
        title,
        price,
        egiftOptions,
        media,
        isTopUp,
      },
    } = this.props;
    // Egift card product image.
    const cartImage = {
      url: (media.length > 0) ? media : undefined,
      alt: title,
      title,
    };
    // Reciepient email.
    const recieptEmail = (typeof egiftOptions.hps_giftcard_recipient_email !== 'undefined')
      ? egiftOptions.hps_giftcard_recipient_email
      : '';
    // Gift card message.
    const giftCardMessage = (typeof egiftOptions.hps_giftcard_message !== 'undefined')
      ? egiftOptions.hps_giftcard_message
      : '';
    // Gift card number for top-up. @todo update the key for card number.
    const cardNumberForTopUp = getCardNumberForTopUpItem(egiftOptions);

    return (
      <div className="product-item egift-cart-item">
        <div className="spc-product-image">
          <CheckoutItemImage img_data={cartImage} />
        </div>
        <div className="spc-product-meta-data">
          <div className="spc-product-title-price">
            <div className="spc-product-title egift-product-title">
              {Drupal.t('Alshaya eGift card', {}, { context: 'egift' })}
            </div>
            <div className="spc-product-price egift-product-price">
              <PriceElement amount={price} />
            </div>
          </div>
          <div className="spc-product-attributes">
            <ConditionalView condition={isTopUp}>
              <div className="spc-cart-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Card No:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{cardNumberForTopUp}</span>
              </div>
            </ConditionalView>
            <ConditionalView condition={isTopUp === false}>
              <div className="spc-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Style:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{title}</span>
              </div>
              <div className="spc-product-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Send to:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value">{ recieptEmail }</span>
              </div>
              <div className="spc-product-attribute egift-cart-message-attribute">
                <span className="spc-cart-product-attribute-label">{Drupal.t('Message:', {}, { context: 'egift' })}</span>
                <span className="spc-cart-product-attribute-value egift-cart-message-value">{ giftCardMessage }</span>
              </div>
            </ConditionalView>
          </div>
        </div>
      </div>
    );
  }
}

export default CheckoutVirtualCartItem;
