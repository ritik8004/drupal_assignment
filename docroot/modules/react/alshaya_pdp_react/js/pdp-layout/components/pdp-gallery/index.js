import React from 'react';

const PdpGallery = (props) => {
  const { skuCode } = props;
  return (
    <>
      <div className="pdp-gallery">{skuCode}</div>
    </>
  );
};
export default PdpGallery;
