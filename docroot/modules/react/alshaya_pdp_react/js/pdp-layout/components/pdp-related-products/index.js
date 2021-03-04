import React from 'react';
import axios from 'axios';
import PdpCrossellUpsell from '../pdp-crossell-upsell';

class PdpRelatedProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      products: null,
      sectionTitle: null,
    };
  }

  componentDidMount() {
    this.getRelatedProducts();
  }

  getRelatedProducts = () => {
    const { products } = this.state;
    const { skuItemCode, type } = this.props;
    const device = (window.innerWidth < 768) ? 'mobile' : 'desktop';

    // Base64 encode sku so the sku with slash doesn't break the endpoint.
    const url = Drupal.url(`related-products/${btoa(skuItemCode)}/${type}/${device}?type=json&cacheable=1`);
    // If related products is already processed.
    if (products === null) {
      axios.get(url).then((response) => {
        if (response.data.length !== 0) {
          this.setState({
            products: response.data.products,
            sectionTitle: response.data.section_title,
          });
        }
      });
    }
  }

  render() {
    const {
      getPanelData, removePanelData, keyId,
    } = this.props;
    const { products, sectionTitle } = this.state;

    return (products) ? (
      <PdpCrossellUpsell
        products={products}
        sectionTitle={sectionTitle}
        getPanelData={getPanelData}
        removePanelData={removePanelData}
        key={keyId}
      />
    ) : null;
  }
}
export default PdpRelatedProducts;
