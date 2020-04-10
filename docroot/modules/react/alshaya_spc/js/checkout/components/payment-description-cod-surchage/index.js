import React from 'react';
import getStringMessage from '../../../utilities/strings';
import PriceElement from '../../../utilities/special-price/PriceElement';

const getCodDescription = (surcharge, messageKey) => {
  const { amount } = surcharge;

  const description = getStringMessage(messageKey);
  if (description.length === 0) {
    return '';
  }

  const descriptionSplit = description.split('[surcharge]');
  return descriptionSplit.reduce((prefix, suffix) => [
    prefix,
    <PriceElement key={messageKey} amount={amount} />,
    suffix,
  ]);
};

const CodSurchargePaymentMethodShortDescription = ({ surcharge, messageKey }) => <div className="spc-payment-method-desc"><div className="desc-content">{getCodDescription(surcharge, messageKey)}</div></div>;

const CodSurchargePaymentMethodDescription = ({ surcharge, messageKey }) => <div className="cod-surcharge-desc">{getCodDescription(surcharge, messageKey)}</div>;

export { CodSurchargePaymentMethodShortDescription, CodSurchargePaymentMethodDescription };
