import React from 'react';
import axios from 'axios';
import PdpDynamicPromotions from '../pdp-dynamic-promotions';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';

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
    const { skuMainCode, promotions } = this.props;
    const { promotionsRawData } = this.state;

    if (!hasValue(promotions)) {
      const url = Drupal.url(`rest/v2/product/${btoa(skuMainCode)}?pdp=magazinev2`);
      const promotionStateValue = promotionsRawData ? promotionsRawData[skuMainCode] : null;
      if (promotionStateValue === null || promotionStateValue === undefined) {
        axios.get(url).then((response) => {
          if (response.data.length !== 0) {
            const promotionsData = promotionsRawData || {};
            promotionsData[skuMainCode] = response.data.promotionsRaw;
            this.setState({
              promotionsRawData: promotionsData,
            });
          }
        });
      }
    }
  }

  render() {
    const {
      skuMainCode,
      cartDataValue,
      promotions,
    } = this.props;
    const { promotionsRawData } = this.state;

    let promotionsData = promotionsRawData ? promotionsRawData[skuMainCode] : null;
    // Check promotions from props.
    if (hasValue(promotions)) {
      promotionsData = promotions;
    }

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
