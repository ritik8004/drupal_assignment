import React from 'react';

const PromotionsWarningModal = ({
  handlePromotionDeselect,
  handlePromotionContinue,
  itemNotEligibleForReturn,
}) => (
  <div className="promotions-warning-modal-wrapper">
    <button type="button" className="close" onClick={() => handlePromotionDeselect()} />
    { !itemNotEligibleForReturn && (
      <>
        <div className="title">
          {Drupal.t('Selected Item is Promotional Item', {}, { context: 'online_returns' })}
        </div>
        <div className="description">
          <span>
            {Drupal.t('To receive refund for promotional items, all items related to the promotion has to be returned.', {}, { context: 'online_returns' })}
          </span>
          <span>
            {Drupal.t('Clicking continue will select all items in this promotion.', {}, { context: 'online_returns' })}
          </span>
        </div>
        <div className="cta-wrapper">
          <button type="button" className="continue-button" onClick={handlePromotionContinue}>
            <span className="continue-button-label">{Drupal.t('Continue', {}, { context: 'online_returns' })}</span>
          </button>
          <button type="button" className="deselect-button" onClick={handlePromotionDeselect}>
            <span className="deselect-button-label">{Drupal.t('Deselect this item', {}, { context: 'online_returns' })}</span>
          </button>
        </div>
      </>
    )}
    { itemNotEligibleForReturn && (
      <>
        <div className="description">
          <span>
            {Drupal.t('One or more products selected are not eligible for a return as they are on promotion. Please select a different product to proceed.', {}, { context: 'online_returns' })}
          </span>
        </div>
      </>
    )}
  </div>
);


export default PromotionsWarningModal;
