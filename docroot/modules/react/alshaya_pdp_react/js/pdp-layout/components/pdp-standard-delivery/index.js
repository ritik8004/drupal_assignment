import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpStandardDelivery = () => {
  const { homeDelivery } = drupalSettings;

  const showHomeDeliveryBlock = () => {
    document.querySelector('.magv2-pdp-standard-delivery-wrapper').classList.toggle('show-home-delivery');
  };

  return (
    <div
      className="magv2-pdp-standard-delivery-wrapper card fadeInUp"
      onClick={() => showHomeDeliveryBlock()}
      style={{ animationDelay: '1s' }}
    >
      <div className="magv2-standard-delivery-title-wrapper">
        <PdpSectionTitle>
          {homeDelivery.title}
        </PdpSectionTitle>
        <div className="magv2-accordion" />
      </div>
      <PdpSectionText className="standard-delivery-detail">
        <span>{homeDelivery.subtitle}</span>
        <span>{homeDelivery.standard_subtitle}</span>
      </PdpSectionText>
    </div>
  );
};

export default PdpStandardDelivery;
