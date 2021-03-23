import React from 'react';
import axios from 'axios';
import parse from 'html-react-parser';

class PdpDynamicPromotions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      label: null,
    };
  }

  componentDidMount() {
    const { skuMainCode, cartDataValue } = this.props;
    this.refreshDynamicPromoLabels(skuMainCode, cartDataValue);
  }

  componentDidUpdate(prevProps) {
    const { skuMainCode, cartDataValue } = this.props;
    if (prevProps.cartDataValue !== cartDataValue) {
      this.refreshDynamicPromoLabels(skuMainCode, cartDataValue);
    }
  }

  refreshDynamicPromoLabels = (skuMainCode, cartDataValue) => {
    if (cartDataValue !== null) {
      const cartDataUrl = Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartDataValue);
      const url = Drupal.url(`rest/v1/promotions/dynamic-label-product/${btoa(skuMainCode)}/?cacheable=1&context=web&${cartDataUrl}`);

      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          this.setState({
            label: response.data.label,
          });
        }
      });
    }
  }

  render() {
    const { label } = this.state;

    return (label) ? (
      <p>{parse(label)}</p>
    ) : null;
  }
}
export default PdpDynamicPromotions;
