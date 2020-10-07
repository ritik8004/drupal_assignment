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
    const { skuMainCode } = this.props;
    const { promotionsRawData } = this.state;
    const url = Drupal.url(`rest/v1/product/${skuMainCode}?pdp=magazinev2`);
    this.getPromotionInfo(promotionsRawData, url);
  }

  componentDidUpdate(prevProps) {
    const { skuMainCode } = this.props;
    // If there is a change in props value (parent sku).
    if (prevProps.skuMainCode !== skuMainCode) {
      const { promotionsRawData } = this.state;
      const url = Drupal.url(`rest/v1/product/${skuMainCode}?pdp=magazinev2`);
      this.getPromotionInfo(promotionsRawData, url);
    }
  }

  getPromotionInfo = (promotionsRawData, url) => {
    // If product promotion data is already processed.
    if (promotionsRawData === null) {
      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          this.setState({
            promotionsRawData: response.data.promotionsRaw,
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

    return (promotionsRawData) ? (
      <>
        {Object.keys(promotionsRawData).map((key) => (
          <p><a href={promotionsRawData[key].promo_web_url}>{promotionsRawData[key].text}</a></p>
        ))}
        <div id="dynamic-promo-labels">
          <PdpDynamicPromotions skuMainCode={skuMainCode} cartDataValue={cartDataValue} />
        </div>
      </>
    ) : null;
  }
}
export default PdpPromotionLabel;
