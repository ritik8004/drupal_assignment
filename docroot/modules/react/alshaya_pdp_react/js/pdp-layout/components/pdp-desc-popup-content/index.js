import React from 'react';
import PdpFullDescription from '../pdp-full-desc';
import PdpInfo from '../pdp-info';

const DescriptionContent = (props) => {
  const {
    skuCode, title, pdpProductPrice, finalPrice, pdpDescription, closeModal,
  } = props;
  const itemCode = Drupal.t('Item Code');

  return (
    <div className="magv2-desc-popup-container">
      <div className="magv2-desc-popup-wrapper">
        <div className="magv2-desc-popup-header-wrapper">
          <a className="close" onClick={() => closeModal()}>
            &times;
          </a>
          <PdpInfo
            title={title}
            finalPrice={finalPrice}
            pdpProductPrice={pdpProductPrice}
            shortDetail="true"
            animateTitlePrice={false}
          />
        </div>
        <div className="magv2-desc-popup-content-wrapper">
          <PdpFullDescription
            pdpDescription={pdpDescription}
          />
          <div className="magv2-desc-popup-pdp-item-code-attribute">
            <span className="magv2-pdp-section-text dark magv2-desc-popup-pdp-item-code-label">{`${itemCode}:`}</span>
            <span className="magv2-pdp-section-text magv2-desc-popup-pdp-item-code-value">{skuCode}</span>
          </div>
        </div>
      </div>
    </div>
  );
};
export default DescriptionContent;
