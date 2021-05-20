import React from 'react';
import axios from 'axios';
import PdpDynamicPromotions from '../pdp-dynamic-promotions';

class PdpPromotionLabel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      promotionsRawData: null,
    };
  }

  componentDidMount() {
    // On first page load.
    this.getPromotionInfo();
  }

  componentDidUpdate(prevProps) {
    const { skuMainCode } = this.props;
    // If there is a change in props value (parent sku).
    if (prevProps.skuMainCode !== skuMainCode) {
      this.getPromotionInfo();
    }
  }

  getPromotionInfo = () => {
    // If product promotion data is already processed.
    const { skuMainCode } = this.props;
    const { promotionsRawData } = this.state;
    const url = Drupal.url(`rest/v2/product/${btoa(skuMainCode)}?pdp=magazinev2`);
    const promotionStateValue = promotionsRawData ? promotionsRawData[skuMainCode] : null;
    if (promotionStateValue === null || promotionStateValue === undefined) {
      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          const promotions = promotionsRawData || {};
          promotions[skuMainCode] = response.data.promotionsRaw;
          this.setState({
            promotionsRawData: promotions,
          });
        }
      });
    }
  }


  render() {
    const {
      skuMainCode,
      cartDataValue,
    } = this.props;
    const { promotionsRawData } = this.state;
    const promotions = promotionsRawData ? promotionsRawData[skuMainCode] : null;

    return (promotions) ? (
      <>
        {Object.keys(promotions).map((key, index) => (
          <p key={`promo-${index + 1}`}><a href={promotions[key].promo_web_url}>{promotions[key].text}</a></p>
        ))}
        <div id="dynamic-promo-labels">
          <PdpDynamicPromotions skuMainCode={skuMainCode} cartDataValue={cartDataValue} />
        </div>
      </>
    ) : null;
  }
}
export default PdpPromotionLabel;
