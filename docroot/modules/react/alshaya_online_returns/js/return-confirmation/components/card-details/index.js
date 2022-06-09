import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardTypeSVG from '../../../../../alshaya_spc/js/svg-component/card-type-svg';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';

const CardDetails = ({
  paymentDetails,
}) => {
  if (!hasValue(paymentDetails)) {
    return null;
  }
  return (
    <>
      <div className="method-list-wrapper">
        {Object.keys(paymentDetails).map((method) => (
          <div key={method} className="method-wrapper">
            <div className="card-icon">
              <CardTypeSVG type={paymentDetails[method].payment_type.toLowerCase()} class={`${paymentDetails[method].payment_type.toLowerCase()} is-active`} />
            </div>
            <div className="card-detail">
              <ConditionalView condition={hasValue(paymentDetails[method].card_type)}>
                <span className="payment-type bold-text">{ paymentDetails[method].card_type }</span>
              </ConditionalView>
              <ConditionalView condition={hasValue(paymentDetails[method].card_number)}>
                <span>
                  {' '}
                  { Drupal.t('ending in', {}, { context: 'online_returns' }) }
                  {' '}
                </span>
                <span className="payment-info bold-text">
                  { Drupal.t('@card_number', { '@card_number': paymentDetails[method].card_number }, {}, { context: 'online_returns' }) }
                </span>
              </ConditionalView>
            </div>
          </div>
        ))}
      </div>
    </>
  );
};

export default CardDetails;
