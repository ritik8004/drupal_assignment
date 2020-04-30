import React from 'react';

import CheckoutItemImage from '../../../utilities/checkout-item-image';
import CheckoutConfigurableOption from '../../../utilities/checkout-configurable-option';
import SpecialPrice from '../../../utilities/special-price';
import ConditionalView from '../../../common/components/conditional-view';

export default class CheckoutCartItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      productInfo: null,
    };
  }

  componentDidMount() {
    const { item } = this.props;

    if (Object.prototype.hasOwnProperty.call(item, 'prepared')) {
      this.setState({
        wait: false,
        productInfo: item,
      });

      return;
    }

    // Key will be like 'product:en:testsku'
    Drupal.alshayaSpc.getProductData(item.sku, this.productDataCallback);
  }

  /**
   * Call back to get product data from storage.
   */
  productDataCallback = (productData) => {
    // If sku info available.
    if (productData !== null && productData.sku !== undefined) {
      this.setState({
        wait: false,
        productInfo: productData,
      });
    }
  };

  render() {
    const { wait } = this.state;
    if (wait === true) {
      return (null);
    }

    const {
      item: {
        id,
        finalPrice,
      },
    } = this.props;

    const {
      productInfo: {
        image,
        options: configurableValues,
        title,
        url: relativeLink,
        price: originalPrice,
      },
    } = this.state;

    const cartImage = {
      url: image,
      alt: title,
      title,
    };

    return (
      <div className="product-item">
        <div className="spc-product-image">
          <CheckoutItemImage img_data={cartImage} />
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
  }
}
