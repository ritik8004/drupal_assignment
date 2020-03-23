import React from 'react';
import ToolTip from '../../../utilities/tooltip';
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
    <>
      <span className="spc-payment-method-desc">
        {getSurchargeShortDescription()}
        <ToolTip enable>{getStringMessage('cod_surcharge_description')}</ToolTip>
      </span>
    </>
  );
};

export default CodSurchargePaymentMethodDescription;
