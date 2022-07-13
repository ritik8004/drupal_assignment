import React from 'react';
import moment from 'moment';
import Cleave from 'cleave.js/react';
import Popup from 'reactjs-popup';
import logger from '../../../../../js/utilities/logger';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import MyEgiftTopUp from '../my-egift-top-up';
import TrashIconSVG from '../../../svg-component/trash-icon-svg';
import { callEgiftApi } from '../../../../../js/utilities/egiftCardHelper';
import PriceElement
  from '../../../../../alshaya_spc/js/utilities/special-price/PriceElement';

class EgiftCardLinked extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      topUpForm: false, // Show / hide top-up form.
      hideCardDetails: false, // Hide card detail only after api for top-up amount ends.
    };
  }

  /**
   * Remove linked card.
   */
  removeCardAction = (e) => {
    e.preventDefault();
    showFullScreenLoader();
    // Call magento API to remove linked eGift card.
    const response = callEgiftApi('eGiftUnlinkCard', 'POST', {});
    if (response instanceof Promise) {
      response.then((result) => {
        removeFullScreenLoader();
        // Check for error from handleResponse.
        if (result.data.error) {
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(result.data) });
        }
        // Check if error from API.
        if (typeof result.data !== 'undefined' && result.data.response_type === false) {
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(result.data) });
        }
        // Remove card if no error response returned.
        if (typeof result.data !== 'undefined' && result.data.response_type) {
          // Calls parent component method to reset and show link new card form.
          const { removeCard } = this.props;
          removeCard();
        }
      });
    }
  }

  /**
   * Handle Top-up button click.
   */
  handleTopUp = (e) => {
    e.preventDefault();
    this.setState({
      topUpForm: true,
    });
  }

  /**
   * Cancel top form and show card details.
   */
  handleCancelTopUp = () => {
    this.setState({
      topUpForm: false,
      hideCardDetails: false,
    });
  }

  handleHideDetails = () => {
    this.setState({
      hideCardDetails: true,
    });
  }

  render() {
    const { linkedCard } = this.props;
    // Return if User linked card data is null.
    if (linkedCard === null) {
      return null;
    }

    const { topUpForm, hideCardDetails } = this.state;

    // Set expired card class.
    let expiredCard = false;
    const currentTimeStamp = Date.now() / 1000;
    if (linkedCard.expiry_date_timestamp < currentTimeStamp) {
      expiredCard = true;
    }

    // Get formatted expiry date.
    moment.locale(drupalSettings.path.currentLanguage);
    const expiryDateFormatted = moment.unix(linkedCard.expiry_date_timestamp).format('Do, MMM YYYY');

    return (
      <div className="egift-card-linked-wrapper">
        <div className="egift-card-linked-wrapper-top">
          <div className="egift-linked-thumbnail">
            <img
              src={linkedCard.card_image}
              className="linked-card-thumbnail"
              alt={linkedCard.card_type}
              title={linkedCard.card_type}
            />
          </div>
          <div className="egift-linked-title-balance-wrapper">
            <div className="egift-linked-title">{Drupal.t('My eGift Card', {}, { context: 'egift' })}</div>
            <div className="egift-linked-balance">
              {Drupal.t('Balance:', {}, { context: 'egift' })}
              <PriceElement amount={parseFloat(linkedCard.current_balance)} showZeroValue />
            </div>
          </div>
          <Popup
            trigger={(
              <button
                id="egift-remove-button"
                type="button"
                className="egift-card-remove"
              >
                <TrashIconSVG />
                <span className="egift-ulink-text">
                  {Drupal.t('Unlink', {}, { context: 'egift' })}
                </span>
              </button>
              )}
            modal
            className="egift-unlink-card-confirm"
            closeOnDocumentClick={false}
          >
            {(close) => (
              <div className="modal">
                <a className="close" onClick={close}>
                  &times;
                </a>
                <p>
                  {Drupal.t('Do you want to unlink this card from this account?', {}, { context: 'egift' })}
                </p>
                <div className="confirm-actions">
                  <button
                    type="button"
                    className="egift-card-remove-confirm-yes"
                    onClick={(e) => this.removeCardAction(e)}
                  >
                    {Drupal.t('Yes')}
                  </button>
                  <button
                    type="button"
                    className="egift-card-remove-confirm-no"
                    onClick={() => close()}
                  >
                    {Drupal.t('No')}
                  </button>
                </div>
              </div>
            )}
          </Popup>
        </div>
        <ConditionalView condition={hideCardDetails === false}>
          <div className="egift-card-linked-wrapper-bottom egifts-form-wrapper" id="card-details">
            <div className="egift-linked-card-number-wrapper">
              <div className="egift-linked-card-number egift-light-text">{Drupal.t('eGift Card Number', {}, { context: 'egift' })}</div>
              <Cleave
                name="egift-linked-card-number-value"
                className="egift-linked-card-number-value egift-dark-text"
                disabled
                value={linkedCard.card_number}
                options={{ blocks: [4, 4, 4, 4] }}
              />
            </div>
            <div className={`egift-linked-expires-wrapper ${expiredCard}`}>
              <div className="egift-linked-expires egift-light-text">{Drupal.t('Expires on', {}, { context: 'egift' })}</div>
              <div
                className={(expiredCard ? 'egift-linked-expires-value expired-card egift-dark-text' : 'egift-linked-expires-value egift-dark-text')}
              >
                {expiryDateFormatted}
              </div>
              {expiredCard && <span>{Drupal.t('This card has expired.', {}, { context: 'egift' })}</span>}
            </div>
            <div className="egift-linked-card-type-wrapper">
              <div className="egift-linked-card-type egift-light-text">{Drupal.t('Card Type', {}, { context: 'egift' })}</div>
              <div className="egift-linked-card-type-value egift-dark-text">{linkedCard.card_type}</div>
            </div>
            <div className="action-buttons">
              <button
                id="egift-topup-button"
                type="button"
                className="egift-topup egift-topup-btn"
                onClick={(e) => this.handleTopUp(e)}
              >
                {Drupal.t('Top up', {}, { context: 'egift' })}
              </button>
            </div>
          </div>
        </ConditionalView>
        <ConditionalView condition={topUpForm}>
          <MyEgiftTopUp
            handleCancelTopUp={this.handleCancelTopUp}
            cardNumber={linkedCard.card_number}
            handleHideDetails={this.handleHideDetails}
          />
        </ConditionalView>
      </div>
    );
  }
}

export default EgiftCardLinked;
