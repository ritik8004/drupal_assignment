import React from 'react';

const PdpDescription = (props) => {
  const { skuCode, pdpDescription } = props;
  console.log(pdpDescription);
  return (
    <>
      <div className="pdp-description-wrapper"></div>
    </>
  );
}
export default PdpDescription;
