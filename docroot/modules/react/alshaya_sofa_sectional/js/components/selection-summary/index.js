import React from 'react';
import SofaSectionalConfigItem from '../common/sofa-sectional-config-item';

const SelectionSummary = () => {
  const SelectionConfigItems = [
    { name: 'Configuration: ', value: 'None Selected' },
    { name: 'Width: ', value: 'None Selected' },
    { name: 'Sectional Depth: ', value: 'None Selected' },
    { name: 'Fabric and Color: ', value: 'Frost Gray, Performance Coastal Linen' },
    { name: 'Leg style: ', value: 'Dark Pewter' },
    { name: 'Delivery: ', value: 'Made to Order (ships in 6-9 weeks)' },
  ];

  const configItems = [];

  const GetSummaryConfig = () => {
    SelectionConfigItems.forEach((config) => {
      configItems.push(
        <SofaSectionalConfigItem
          name={config.name}
          value={config.value}
        />,
      );
    });
    return configItems;
  };

  return (
    <div className="sofa-section-card sofa-selection-summary-wrapper">
      <div className="sofa-selection-summary-title">
        {Drupal.t('your selection summary')}
      </div>
      <div className="sofa-selection-summary-subtitle">
        {Drupal.t('Andes 3-Piece Chaise Sectional')}
      </div>
      <div className="sofa-selection-summary-image-preview">
        <img
          src="https://assets.weimgs.com/weimgs/ab/images/wcm/products/202133/0009/img7d.jpg"
          alt="product image"
          title="product image"
          loading="lazy"
        />
      </div>
      {GetSummaryConfig()}
    </div>
  );
};
export default SelectionSummary;
