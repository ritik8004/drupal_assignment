import React from 'react';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import CardTypeSVG from '../../../../../alshaya_spc/js/svg-component/card-type-svg';

const EgiftCardDetails = ({
  cardList, selectedOption, egiftCardType,
}) => {
  let selected = cardList.card_number;
  if (hasValue(selectedOption)) {
    selected = selectedOption;
  }
  // Defining variable to check whether current page is return confirmation or not.
  const isReturnConfPage = window.location.href.indexOf('return-confirmation');
  // Custom function for refund message to avoid the nested ternary expressions.
  const RefundMessage = ({ data }) => ((data === -1)
    ? (
      <div className="refund-message">
        {Drupal.t('Your refund will be credited immediately after the item is returned to warehouse', {}, { context: 'online_returns' })}
      </div>
    )
    : <></>);

  return (
    <>
      <div className="method-list-wrapper">
        <div className="method-wrapper" key={`${cardList.card_number}`}>
          {hasValue(selected) && (isReturnConfPage === -1)
            ? (
              <input
                type="radio"
                value={`${cardList.card_number}`}
                name={`${cardList.card_number}`}
                checked={selected === `${cardList.card_number}`}
              />
            )
            : (
              <></>
            )}
          <div className="card-icon">
            <CardTypeSVG type="egift" class={`${cardList.card_number} is-active`} />
          </div>
          <div className="egift-card-detail">
            <span>
              {Drupal.t('eGift Card', {}, { context: 'online_returns' })}
            </span>
          </div>
        </div>
      </div>
      {egiftCardType && (isReturnConfPage === -1)
        ? (
          <div className="refund-message">
            {Drupal.t('Details of your eGift Card will be sent to your email address "@email"', { '@email': drupalSettings.userDetails.userEmailID }, { context: 'online_returns' })}
          </div>
        )
        : <RefundMessage data={isReturnConfPage} />}
    </>
  );
};

export default EgiftCardDetails;
