import React from 'react';

import CartPromotion from '../../cart/components/cart-promotion';

export default class RecommendedProduct extends React.Component {

  render() {
    const item = this.props.item;
    return (
      <a href={"product-quick-view/" + item.nid + "/nojs"} className='use-ajax above-mobile-block' data-dialog-type='modal'>
        <img src={item.extra_data.cart_image.url} alt={item.extra_data.cart_image.alt} title={item.extra_data.cart_image.title} />
        <div>{item.title}</div>
        <div className="spc-promotions">
          {item.promo.map((key, val) =>
            <CartPromotion key={val} promo={key} />
          )}
        </div>
      </a>
    );
  }
}
