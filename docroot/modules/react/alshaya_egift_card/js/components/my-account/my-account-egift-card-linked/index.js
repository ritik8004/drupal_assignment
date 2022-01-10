import React from 'react';
import PriceElement
  from '../../../../../js/utilities/components/price/price-element';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import logger from '../../../../../js/utilities/logger';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';
import ConditionalView
  from '../../../../../js/utilities/components/conditional-view';
import MyEgiftTopUp from '../my-egift-top-up';
import TrashIconSVG from '../../../../../alshaya_spc/js/svg-component/trash-icon-svg';

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
    return callMagentoApi('/V1/egiftcard/unlinkcard', 'POST', {})
      .then((response) => {
        removeFullScreenLoader();
        // Check for error from handleResponse.
        if (typeof response.data !== 'undefined' && typeof response.data.error !== 'undefined' && response.data.error) {
          logger.error('Error while unlinking card. @error', { '@error': JSON.stringify(response.data) });
        }

        // Remove card if no error response returned.
        if (typeof response.data !== 'undefined' && response.data.response_type) {
          // Calls parent component method to reset and show link new card form.
          const { removeCard } = this.props;
          removeCard();
        }
      });
  }

  /**
   * Get day in st, nd, rd, th format.
   */
  dateNthFormat = (d) => {
    if (d > 3 && d < 21) return `${d}th`;
    switch (d % 10) {
      case 1: {
        return `${d}st`;
      }
      case 2: {
        return `${d}nd`;
      }
      case 3: {
        return `${d}rd`;
      }
      default: {
        return `${d}th`;
      }
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
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const expiryDate = new Date(linkedCard.expiry_date);
    const expiryDateFormatted = `${this.dateNthFormat(expiryDate.getDate())}, ${months[expiryDate.getMonth()]} ${expiryDate.getFullYear()}`;

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
              {Drupal.t('Balanace:', {}, { context: 'egift' })}
              <PriceElement amount={parseFloat(linkedCard.current_balance)} />
            </div>
          </div>
          <button
            id="egift-remove-button"
            type="button"
            className="egift-card-remove"
            onClick={(e) => this.removeCardAction(e)}
          >
            <TrashIconSVG />
          </button>
        </div>
        <ConditionalView condition={hideCardDetails === false}>
          <div className="egift-card-linked-wrapper-bottom egifts-form-wrapper" id="card-details">
            <div className="egift-linked-card-number-wrapper">
              <div className="egift-linked-card-number egift-light-text">{Drupal.t('Gift Card number', {}, { context: 'egift' })}</div>
              <div className="egift-linked-card-number-value egift-dark-text">{linkedCard.card_number}</div>
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
