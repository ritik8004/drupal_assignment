import React from 'react';
import ConditionalView from '../../common/components/conditional-view';
import VatText from '../vat-text';

const DeliveryVATSuffix = (props) => {
  const {
    shippingAmount,
    showVatText,
  } = props;
  return (
    <div className="delivery-vat">
      <ConditionalView condition={shippingAmount === null}>
        <span className="delivery-prefix">{Drupal.t('Excluding delivery')}</span>
      </ConditionalView>
      {/* If Aura Totals are present VAT text is shown as part of balance payable */}
      {showVatText === true
        ? null
        : <VatText />}
    </div>
  );
};

export default DeliveryVATSuffix;
