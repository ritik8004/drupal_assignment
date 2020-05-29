import React from 'react';
import ConditionalView from '../../../common/components/conditional-view';
import PdpDetail from '../pdp-detail';

export default class PdpHeader extends React.PureComponent {
  render() {
    const {
      title, pdpProductPrice, finalPrice,
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
          <div className="cart-icon" />
        </ConditionalView>
      </div>
    );
  }
}
