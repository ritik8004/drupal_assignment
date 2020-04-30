import React from 'react';
import PdpGallery from '../pdp-gallery';

export default class PdpLayout extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      sku: null,
      pdpGallery: null,
    };
  }

  componentDidMount() {
    let data = window.drupalSettings.pdpGallery;
    let skuItemCode = Object.keys(data)[0];
    this.setState({
      sku: skuItemCode,
      pdpGallery: data,
    });

  }

  render() {
    const { sku, pdpGallery } = this.state;

    return (
      <>
        <div className="pdp-layout-wrapper">Item Code: {sku}</div>
        {(sku &&  pdpGallery) && (
          <PdpGallery skuCode={sku} pdpGallery={pdpGallery} />
        )}

      </>
    );
  }
}
