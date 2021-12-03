import React from 'react';
import EgiftCardOpenAmountField from '../egift-card-open-amount-field';
import getCurrencyCode from '../../../../../js/utilities/util';

const EgiftCardAmount = (props) => {
  const { selected } = props;
  const amounts = selected.extension_attributes.hps_giftcard_amount;

  const listItems = amounts.map((amount) => (<li key={amount.value}>{amount.value}</li>));

  return (
    <div className="egift-card-amounts">
      <p>
        {
          Drupal.t('Amount @currencyCode', {
            '@currencyCode': getCurrencyCode(),
          }, { context: 'egift' })
        }
      </p>
      <ul>
        {listItems}
      </ul>
      <EgiftCardOpenAmountField selected={selected} />
    </div>
  );
};

export default EgiftCardAmount;
