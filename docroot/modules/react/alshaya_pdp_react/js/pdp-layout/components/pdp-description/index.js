import React from 'react';

const PdpDescription = (props) => {
  const { skuCode } = props;

  return (
    <>
      <div className="pdp-description-wrapper">{skuCode}</div>
    </>
  );
};
export default PdpDescription;
