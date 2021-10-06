import React from 'react';
import SofaSectionalConfigItem from '../common/sofa-sectional-config-item';

const SelectionSummary = () => (
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
    <SofaSectionalConfigItem
      name="Configuration: "
      value="None Selected"
    />
    <SofaSectionalConfigItem
      name="Width: "
      value="None Selected"
    />
    <SofaSectionalConfigItem
      name="Sectional Depth: "
      value="None Selected"
    />
    <SofaSectionalConfigItem
      name="Fabric and Color: "
      value="Frost Gray, Performance Coastal Linen"
    />
    <SofaSectionalConfigItem
      name="Leg style: "
      value="Dark Pewter"
    />
    <SofaSectionalConfigItem
      name="Delivery: "
      value="Made to Order (ships in 6-9 weeks)"
    />
  </div>
);

export default SelectionSummary;
