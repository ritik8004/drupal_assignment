import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpDetail from '../pdp-detail';

export default class PdpHeader extends React.PureComponent {
  render() {
    const {
      title, pdpProductPrice, shortDetail = false, finalPrice, brandLogo, brandLogoAlt, brandLogoTitle,
    } = this.props;

    return (
      <div className="magv2-header-wrapper">
        <ConditionalView condition={window.innerWidth < 768}>
          <div className="back-button" />
          <PdpDetail
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
          <PdpDetail
            title={title}
            brandLogo={brandLogo}
            brandLogoAlt={brandLogoAlt}
            brandLogoTitle={brandLogoTitle}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
          />
        </ConditionalView>
      </div>
    );
  }
}
