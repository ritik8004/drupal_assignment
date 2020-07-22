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

  getRelatedProducts = (products, url) => {
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
    const { type, skuItemCode } = this.props;
    const device = (window.innerWidth < 768) ? 'mobile' : 'desktop';
    const url = Drupal.url(`related-products/${skuItemCode}/${type}/${device}?type=json`);
    const { products, sectionTitle } = this.state;

    this.getRelatedProducts(products, url);

    return (products) ? (
      <PdpCrossellUpsell products={products} sectionTitle={sectionTitle} />
    ) : null;
  }
}
export default PdpRelatedProducts;
