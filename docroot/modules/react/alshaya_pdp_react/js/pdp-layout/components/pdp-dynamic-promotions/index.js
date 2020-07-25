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
    const { skuMainCode } = this.props;
    this.refreshDynamicPromoLabels(skuMainCode);
  }

  componentDidUpdate(prevProps) {
    const { skuMainCode } = this.props;
    if (prevProps.skuMainCode !== skuMainCode) {
      this.refreshDynamicPromoLabels(skuMainCode);
    }
  }

  refreshDynamicPromoLabels = (skuMainCode) => {
    const cartData = Drupal.alshayaSpc.getCartData();
    if (cartData !== null) {
      const cartDataUrl = Drupal.alshayaSpc.getCartDataAsUrlQueryString(cartData);
      const url = Drupal.url(`promotions/dynamic-label-product/${skuMainCode}/?cacheable=1&${cartDataUrl}`);

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
