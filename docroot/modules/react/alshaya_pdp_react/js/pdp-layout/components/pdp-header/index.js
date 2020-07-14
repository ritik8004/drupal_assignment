import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpInfo from '../pdp-info';
import { addToCartConfigurable, addToCartSimple } from '../../../utilities/pdp_layout';


export default class PdpHeader extends React.PureComponent {
  render() {
    const {
      title,
      pdpProductPrice,
      finalPrice,
      brandLogo,
      brandLogoAlt,
      brandLogoTitle,
      skuCode,
      productInfo,
      configurableCombinations,
    } = this.props;

    return (
      <div className="magv2-header-wrapper">
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="back-button" />
          <PdpInfo
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
            shortDetail="true"
          />
          <div id="alshaya_react_mini_cart">
            <div id="mini-cart-wrapper">
              <div className="acq-mini-cart">
                <a className="cart-link" href="/en/cart" />
              </div>
            </div>
            <div id="cart_notification" />
          </div>
        </ConditionalView>
        <ConditionalView condition={window.innerWidth > 767}>
          <PdpInfo
            title={title}
            brandLogo={brandLogo}
            brandLogoAlt={brandLogoAlt}
            brandLogoTitle={brandLogoTitle}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
          />
          <div id="sticky-header-btn">
            <div className="magv2-add-to-basket-container" ref={this.button}>
              {(configurableCombinations) ? (
                <button
                  className="magv2-button"
                  type="submit"
                  id="add-to-cart-sticky"
                  onClick={(e) => addToCartConfigurable(e, 'add-to-cart-sticky', configurableCombinations, skuCode, productInfo)}
                >
                  {Drupal.t('Add To Bag')}
                </button>
              ) : (
                <button
                  className="magv2-button"
                  type="submit"
                  id="add-to-cart-sticky"
                  onClick={(e) => addToCartSimple(e, 'add-to-cart-sticky', skuCode, productInfo)}
                >
                  {Drupal.t('Add To Bag')}
                </button>
              )}
            </div>
          </div>
        </ConditionalView>
      </div>
    );
  }
}
