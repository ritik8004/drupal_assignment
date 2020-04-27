import React from 'react';

const PdpGallery = (props) => {
  const { skuCode, productInfo } = props;
  return (
    <>
      <div className="pdp-gallery"><div dangerouslySetInnerHTML={ { __html: productInfo[skuCode] ? productInfo[skuCode].gallery : <React.Fragment></React.Fragment>} }></div></div>
    </>
  );
}
export default PdpGallery;
