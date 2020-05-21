import React from 'react';
import PdpFullDescription from '../pdp-full-desc';
import PdpDetail from '../pdp-detail';

const DescriptionContent = (props) => {
  const {
    skuCode, title, pdpProductPrice, finalPrice, pdpDescription, closeModal,
  } = props;

  return (
    <div className="magv2-desc-popup-container">
      <div className="magv2-desc-popup-wrapper">
        <div className="magv2-desc-popup-header-wrapper">
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
          <PdpDetail
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
            shortDetail="true"
          />
        </div>
        <div className="magv2-desc-popup-content-wrapper">
          <div className="magv2-desc-popup-pdp-item-code-attribute">
            <span className="magv2-pdp-section-text dark magv2-desc-popup-pdp-item-code-label">Item Code:</span>
            <span className="magv2-pdp-section-text magv2-desc-popup-pdp-item-code-value">{skuCode}</span>
          </div>
          <PdpFullDescription
            pdpDescription={pdpDescription}
          />
        </div>
      </div>
    </div>
  );
};
export default DescriptionContent;
