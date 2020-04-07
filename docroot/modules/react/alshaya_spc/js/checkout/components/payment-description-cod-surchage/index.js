import React from 'react';
import getStringMessage from '../../../utilities/strings';
import PriceElement from '../../../utilities/special-price/PriceElement';

const CodSurchargePaymentMethodDescription = ({ surcharge }) => {
  const getSurchargeShortDescription = () => {
    const { amount } = surcharge;

    const description = getStringMessage('cod_surcharge_short_description');
    if (description.length === 0) {
      return '';
    }

    const descriptionSplit = description.split('[surcharge]');
    return descriptionSplit.reduce((prefix, suffix) => [
      prefix,
      <PriceElement key="cod_surcharge_short_description" amount={amount} />,
      suffix,
    ]);
  };

  if (getSurchargeShortDescription() === false) {
    return '';
  }

  return (
    <div className="spc-payment-method-desc">
      <div className="desc-content">{getSurchargeShortDescription()}</div>
    </div>
  );
};

export default CodSurchargePaymentMethodDescription;
