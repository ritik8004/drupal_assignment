import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import SpecialPrice from '../../../utilities/special-price';
import ConditionalView from "../../../common/components/conditional-view";

const CheckoutCartItem = (props) => {
  const {
    item: {
      title,
      relative_link,
      configurable_values,
      extra_data,
      original_price,
      final_price,
    },
  } = props;
  return (
    <div className="product-item">
      <div className="spc-product-image">
        <ConditionalView condition={extra_data.cart_image !== null}>
          <CheckoutItemImage img_data={extra_data.cart_image} />
        </ConditionalView>
      </div>
      <div className="spc-product-meta-data">
        <div className="spc-product-title-price">
          <div className="spc-product-title">
            <ConditionalView condition={relative_link.length > 0}>
              <a href={relative_link}>{title}</a>
            </ConditionalView>
            <ConditionalView condition={relative_link.length === 0}>
              {title}
            </ConditionalView>
          </div>
          <div className="spc-product-price">
            <SpecialPrice price={original_price} final_price={final_price} />
          </div>
        </div>
        <div className="spc-product-attributes">
          { configurable_values.map((key) => <CheckoutConfigurableOption key={`${key}-${Math.floor(Math.random() * 99)}`} label={key} />) }
        </div>
      </div>
    </div>
  );
};

export default CheckoutCartItem;
