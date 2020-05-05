import React from 'react';
import getStringMessage from '../../../utilities/strings';
import PriceElement from '../../../utilities/special-price/PriceElement';
import { replaceCodTokens } from '../../../utilities/checkout_util';

const CodSurchargeInformation = ({ surcharge: { amount }, messageKey }) => {
  const description = getStringMessage(messageKey);
  if (description.length === 0) {
    return '';
  }

  return replaceCodTokens(<PriceElement key={messageKey} amount={amount} />, description);
};

export default CodSurchargeInformation;
