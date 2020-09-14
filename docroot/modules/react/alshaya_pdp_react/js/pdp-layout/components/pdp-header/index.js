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
      context,
    } = this.props;

    const { checkoutFeatureStatus } = drupalSettings;

    // Back arrow for mobile.
    const currentUrl = window.location.href;
    const currentDomain = currentUrl.replace('http://', '').replace('https://', '').split(/[/?#]/)[0];
    const previousLink = document.referrer;
    const previousDomain = previousLink.replace('http://', '').replace('https://', '').split(/[/?#]/)[0];
    let backArrow = '';
    if (currentDomain === previousDomain) {
      backArrow = previousLink;
    }

    return (
      <div className="magv2-header-wrapper">
        <ConditionalView condition={window.innerWidth < 768}>
          {backArrow ? (
            <a className="back-button" href={backArrow} />
          ) : null}
          <PdpInfo
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
            shortDetail="true"
            animateTitlePrice
          />
          <div id="block-alshayareactcartminicartblock" dataBlockPluginId="alshaya_react_mini_cart" className="block block-alshaya-spc block-alshaya-react-mini-cart">
            <div id="mini-cart-wrapper">
              <div className="acq-mini-cart">
                <a className="cart-link" href="/en/cart" />
              </div>
            </div>
            <div id="magv2_cart_notification" />
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
            animateTitlePrice
          />
          {(checkoutFeatureStatus === 'enabled') ? (
            <div id="sticky-header-btn">
              <div className="magv2-add-to-basket-container" ref={this.button}>
                {(configurableCombinations) ? (
                  <button
                    className="magv2-button"
                    type="submit"
                    id="add-to-cart-sticky"
                    onClick={(e) => addToCartConfigurable(e, 'add-to-cart-sticky', configurableCombinations, skuCode, productInfo, context)}
                  >
                    {Drupal.t('Add To Bag')}
                  </button>
                ) : (
                  <button
                    className="magv2-button"
                    type="submit"
                    id="add-to-cart-sticky"
                    onClick={(e) => addToCartSimple(e, 'add-to-cart-sticky', skuCode, productInfo, context)}
                  >
                    {Drupal.t('Add To Bag')}
                  </button>
                )}
              </div>
            </div>
          ) : null}
        </ConditionalView>
      </div>
    );
  }
}
