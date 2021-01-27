import React from 'react';
import ConditionalView from '../../common/components/conditional-view';
import VatText from '../vat-text';

const DeliveryVATSuffix = (props) => {
  const {
    shippingAmount,
    showVatTextAsSuffix,
  } = props;
  return (
    <div className="delivery-vat">
      <ConditionalView condition={shippingAmount === null}>
        <span className="delivery-prefix">{Drupal.t('Excluding delivery')}</span>
      </ConditionalView>
      {/* If any other Hero total are present VAT text is shown as part of the
       last hero total and not the usual order total */}
      {showVatTextAsSuffix === true
        ? null
        : <VatText />}
    </div>
  );
};

export default DeliveryVATSuffix;
