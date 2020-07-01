import React from 'react';
import PdpInfo from '../pdp-info';

const PdpCrossellUpsellImage = ({
  imageUrl, alt, title, pdpProductPrice, finalPrice, productUrl,
}) => (
  <a className="magv2-pdp-crossell-upsell-image-wrapper" href={productUrl}>
    <img
      src={imageUrl}
      alt={alt}
      title={title}
    />
    <PdpInfo
      title={title}
      finalPrice={finalPrice}
      pdpProductPrice={pdpProductPrice}
    />
  </a>
);

export default PdpCrossellUpsellImage;
