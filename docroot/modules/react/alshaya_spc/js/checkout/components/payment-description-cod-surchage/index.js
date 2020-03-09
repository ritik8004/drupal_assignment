import React from 'react';
import ToolTip from "../../../utilities/tooltip";
import {getStringMessage} from "../../../utilities/strings";
import PriceElement from "../../../utilities/special-price/PriceElement";

const CodSurchargePaymentMethodDescription = ({surcharge}) => {
  const getSurchargeShortDescription = () => {
    let {amount} = surcharge;

    let description = getStringMessage('cod_surcharge_short_description');
    if (description.length == 0) {
      return '';
    }

    let descriptionSplit = description.split('[surcharge]');
    return descriptionSplit.reduce((prefix, suffix) => {
      return [
        prefix,
        <PriceElement key="cod_surcharge_short_description" amount={amount} />,
        suffix,
      ];
    });
  };

  if (getSurchargeShortDescription() === false) {
    return '';
  }

  return (
    <React.Fragment>
    <span className="spc-payment-method-desc">
      {getSurchargeShortDescription()}
      <ToolTip content={getStringMessage('cod_surcharge_description')} enable />
    </span>
    </React.Fragment>
  );
};

export default CodSurchargePaymentMethodDescription;
