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

const CodSurchargeInformation = ({ surcharge, messageKey }) => (<div>{getCodDescription(surcharge, messageKey)}</div>);

export default CodSurchargeInformation;
