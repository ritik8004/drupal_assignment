import React from 'react';
import PdpInfo from '../pdp-info';

const PdpCrossellUpsellImage = ({
  imageUrl, alt, title, pdpProductPrice, finalPrice,
}) => (
  <a className="magv2-pdp-crossell-upsell-image-wrapper">
    <img
      src={imageUrl}
      alt={alt}
      title={title}
    />
    <PdpInfo
      title={title}
      finalPrice={finalPrice}
      pdpProductPrice={pdpProductPrice}
      shortDetail="true"
    />
  </a>
);

export default PdpCrossellUpsellImage;
