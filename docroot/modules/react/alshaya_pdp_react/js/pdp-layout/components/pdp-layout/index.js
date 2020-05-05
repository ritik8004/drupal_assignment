import React from 'react';
import PdpGallery from '../pdp-gallery';
import PdpDescription from '../pdp-description';

const PdpLayout = () => {
  let skuItemCode = null;
  const { pdpGallery } = drupalSettings;
  if (pdpGallery) {
    [skuItemCode] = Object.keys(pdpGallery);
  }
  render() {
    const { sku, pdpGallery } = this.state;
    const emptyRes = (
      <div>Product data not available</div>
    );

    return (sku &&  pdpGallery) ?
    <React.Fragment> <PdpGallery skuCode={sku} pdpGallery={pdpGallery} ></PdpGallery> 
    <PdpDescription skuCode={sku} pdpDescription={pdpGallery} ></PdpDescription>
    </React.Fragment> : emptyRes;
  }
};
