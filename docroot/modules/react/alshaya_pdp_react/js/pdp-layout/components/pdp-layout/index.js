import React from 'react';
import PdpGallery from '../pdp-gallery';

export default class PdpLayout extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      sku: null,
      productInfo: null,
    };
  }

  componentDidMount() {
    let data = window.drupalSettings.productInfo;
    let skuItemCode = Object.keys(data)[0];
    this.setState({
      sku: skuItemCode,
      productInfo: data,
    });

  }

  render() {
    const { sku, productInfo } = this.state;

    return (
      <>
        <div className="pdp-layout-wrapper">Item Code: {sku}</div>
        {(sku &&  productInfo) && (
          <PdpGallery skuCode={sku} productInfo={productInfo} />
        )}

      </>
    );
  }
}
