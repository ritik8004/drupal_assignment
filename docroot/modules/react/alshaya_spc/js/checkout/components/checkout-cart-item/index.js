import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import SpecialPrice from '../../../utilities/special-price';
import ConditionalView from '../../../common/components/conditional-view';

const CheckoutCartItem = (props) => {
  const {
    item: {
      id,
      title,
      relative_link: relativeLink,
      configurable_values: configurableValues,
      extra_data,
      original_price: originalPrice,
      final_price: finalPrice,
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
            <ConditionalView condition={relativeLink.length > 0}>
              <a href={relativeLink}>{title}</a>
            </ConditionalView>
            <ConditionalView condition={relativeLink.length === 0}>
              {title}
            </ConditionalView>
          </div>
          <div className="spc-product-price">
            <SpecialPrice price={originalPrice} final_price={finalPrice} />
          </div>
        </div>
        <div className="spc-product-attributes">
          { configurableValues.map((key) => <CheckoutConfigurableOption key={`${key.label}-${id}`} label={key} />) }
        </div>
      </div>
    </div>
  );
};

export default CheckoutCartItem;
