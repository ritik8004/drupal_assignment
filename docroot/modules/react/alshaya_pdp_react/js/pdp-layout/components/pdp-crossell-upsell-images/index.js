import React from 'react';
import LazyLoad from 'react-lazy-load';
import PdpInfo from '../pdp-info';

const PdpCrossellUpsellImage = ({
  imageUrl, alt, title, pdpProductPrice, finalPrice, productUrl,
}) => (
  <a className="magv2-pdp-crossell-upsell-image-wrapper" href={productUrl}>
    <LazyLoad
      debounce={false}
      throttle={250}
      offsetTop={0}
    >
      <img
        src={imageUrl}
        alt={alt}
        title={title}
      />
    </LazyLoad>
    <PdpInfo
      title={title}
      finalPrice={finalPrice}
      pdpProductPrice={pdpProductPrice}
    />
  </a>
);

export default PdpCrossellUpsellImage;
