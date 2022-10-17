import React from 'react';
import axios from 'axios';
import PdpDynamicPromotions from '../pdp-dynamic-promotions';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import getPdpPromotionV2Labels from '../../../utilities/pdpPromotionV2Label';

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

  async componentDidUpdate(prevProps) {
    const { skuMainCode } = this.props;
    // If there is a change in props value (parent sku).
    if (prevProps.skuMainCode !== skuMainCode) {
      // Get product promotions from graphQL if V3 is enabled.
      if (hasValue(drupalSettings.alshayaRcs)) {
        const promotion = await getPdpPromotionV2Labels(skuMainCode);
        this.getPromotionInfo(promotion);
      } else {
        this.getPromotionInfo();
      }
    }
  }

  getPromotionInfo = (promotion) => {
    const { skuMainCode } = this.props;
    let promotionsData = {};
    // Get product promotions from V2 if V3 is not enabled.
    if (!hasValue(drupalSettings.alshayaRcs)) {
      const { promotionsRawData } = this.state;
      const url = Drupal.url(`rest/v2/product/${btoa(skuMainCode)}?pdp=magazinev2`);
      const promotionStateValue = promotionsRawData ? promotionsRawData[skuMainCode] : null;
      if (promotionStateValue === null || promotionStateValue === undefined) {
        axios.get(url).then((response) => {
          if (response.data.length !== 0) {
            promotionsData = promotionsRawData || {};
            promotionsData[skuMainCode] = response.data.promotionsRaw;
            this.setState({
              promotionsRawData: promotionsData,
            });
          }
        });
      }
    } else {
      // Get product promotions from V3.
      promotionsData[skuMainCode] = (hasValue(promotion)) ? promotion : null;
      this.setState({
        promotionsRawData: promotionsData,
      });
    }
  }

  render() {
    const {
      skuMainCode,
      cartDataValue,
    } = this.props;
    const { promotionsRawData } = this.state;
    const promotionsData = promotionsRawData ? promotionsRawData[skuMainCode] : null;

    return (promotionsData) ? (
      <>
        {Object.keys(promotionsData).map((key) => (
          <p key={key}>
            <a href={promotionsData[key].promo_web_url}>{promotionsData[key].text}</a>
          </p>
        ))}
        <div id="dynamic-promo-labels">
          <PdpDynamicPromotions skuMainCode={skuMainCode} cartDataValue={cartDataValue} />
        </div>
      </>
    ) : null;
  }
}

export default PdpPromotionLabel;
