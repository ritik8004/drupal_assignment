import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';

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
    <React.Fragment>
        <div className="pdp-layout-wrapper">Item Code: {sku}</div>
        {(sku &&  pdpGallery) && (
          <React.Fragment>
            <PdpGallery skuCode={sku} pdpGallery={pdpGallery} ></PdpGallery>
            <PdpDescription skuCode={sku} pdpDescription={pdpGallery} ></PdpDescription>
          </React.Fragment>
        )}

    </React.Fragment>
    );
  }
}
