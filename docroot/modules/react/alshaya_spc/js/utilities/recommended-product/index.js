import React from 'react';

import CartPromotion from '../../cart/components/cart-promotion';

export default class RecommendedProduct extends React.Component {

  render() {
    const item = this.props.item;
    const item_url = 'product-quick-view/' + item.nid + '/nojs';
    return (
      <a href={Drupal.url(item_url)} className='use-ajax above-mobile-block recommended-product' data-dialog-type='modal'>
        {item.extra_data.cart_image !== undefined ?
          <img src={item.extra_data.cart_image.url} alt={item.extra_data.cart_image.alt} title={item.extra_data.cart_image.title} />
          : null}
        <div className="product-title">{item.title}</div>
        <div className="spc-promotions">
          {item.promo.map((key, val) =>
            <CartPromotion key={val} promo={key} />
          )}
        </div>
      </a>
    );
  }
}
