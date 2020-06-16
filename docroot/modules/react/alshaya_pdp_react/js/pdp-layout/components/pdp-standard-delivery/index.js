import React from 'react';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';

const PdpStandardDelivery = () => {
  const { homeDelivery } = drupalSettings;

  return (
    <div className="magv2-pdp-standard-delivery-wrapper card">
      <div className="magv2-standard-delivery-title-wrapper">
        <PdpSectionTitle>
          {homeDelivery.title}
          <span className="standard-delivery-title-tag free-tag">{Drupal.t('free')}</span>
        </PdpSectionTitle>
      </div>
      <PdpSectionText className="standard-delivery-detail">
        <span>{homeDelivery.subtitle}</span>
        <span>{homeDelivery.standard_subtitle}</span>
      </PdpSectionText>
    </div>
  );
};

export default PdpStandardDelivery;
