import React from 'react';
import SofaSectionalSummaryConfigItem from '../common/sofa-sectional-summary-config-item';

const SelectionSummary = (props) => {
  const {
    selectedAttributes,
    configurableAttributes,
    selectedVariant,
    productInfo,
  } = props;

  const selectAttributeKeys = Object.keys(selectedAttributes);

  if (selectAttributeKeys === null) {
    return (null);
  }

  const configurableAttributeKey = Object.keys(configurableAttributes);

  if (configurableAttributeKey.length !== selectAttributeKeys.length) {
    return (null);
  }

  const SelectionConfigItems = [];

  Object.entries(selectedAttributes).forEach(([key, attributeId]) => {
    let configItem = {};
    const { label, values } = configurableAttributes[key];
    values.forEach((attribute) => {
      if (attribute.value_id === attributeId) {
        configItem = {
          name: label,
          value: attribute.label,
        };
        SelectionConfigItems.push(configItem);
      }
    });
  });

  const configItems = [];

  const GetSummaryConfig = () => {
    SelectionConfigItems.forEach((config) => {
      configItems.push(
        <SofaSectionalSummaryConfigItem
          key={config.name}
          name={config.name}
          value={config.value}
        />,
      );
    });
    return configItems;
  };

  const { variants } = productInfo;
  const productDetails = variants[selectedVariant];

  return (
    <div className="sofa-section-card sofa-selection-summary-wrapper">
      <div className="sofa-selection-summary-title">
        {Drupal.t('your selection summary')}
      </div>
      <div className="sofa-selection-summary-subtitle">
        {productDetails.cart_title}
      </div>
      <div className="sofa-selection-summary-image-preview">
        <img
          src={productDetails.cart_image}
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
