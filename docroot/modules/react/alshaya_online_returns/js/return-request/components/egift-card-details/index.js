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

  return (
    <>
      <div className="method-list-wrapper">
        <div className="method-wrapper" key={`${cardList.card_number}`}>
          {hasValue(selectedOption)
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
      {egiftCardType
        ? (
          <div className="refund-message">
            {Drupal.t('Details of your eGift Card will be sent to your email address "@email"', { '@email': drupalSettings.userDetails.userEmailID }, { context: 'online_returns' })}
          </div>
        )
        : (
          <div className="refund-message">
            {Drupal.t('Your refund will be credited immediately after the item is returned to warehouse', {}, { context: 'online_returns' })}
          </div>
        )}
    </>
  );
};

export default EgiftCardDetails;
