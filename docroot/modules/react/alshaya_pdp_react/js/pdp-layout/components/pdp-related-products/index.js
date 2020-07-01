import React from 'react';
import PdpCrossellUpsell from '../pdp-crossell-upsell';

const PdpRelatedProducts = (props) => {
  const { type } = props;
  const { relatedProducts } = drupalSettings;
  const { sectionTitle } = relatedProducts[type];
  const { products } = relatedProducts[type];

  return (
    <PdpCrossellUpsell products={products} sectionTitle={sectionTitle} />
  );
};
export default PdpRelatedProducts;
