import React from 'react';
import Popup from 'reactjs-popup';
import axios from 'axios';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
  validateCvv,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import SavedCardsList from './components/SavedCardsList';
import NewCard from './components/NewCard';
import SelectedCard from './components/SelectedCard';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import { handleValidationMessage } from '../../../utilities/form_item_helper';
import WithModal from '../with-modal';
import { CheckoutComUpapiContext } from '../../../context/CheckoutComUpapi';
import { allowSavedCcForTopUp } from '../../../utilities/egift_util';

class PaymentMethodCheckoutComUpapi extends React.Component {
  static contextType = CheckoutComUpapiContext;

  componentDidMount() {
    this.updateCurrentContext({
      cvvValid: !(drupalSettings.checkoutComUpapi.cvvCheck === true),
    });

    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  }

  componentDidUpdate() {
    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  }

  labelEffect = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  };

  cvvValidations = (e) => {
    const cvv = e.target.value.trim();
    const valid = validateCvv(cvv);
    handleValidationMessage(
      'spc-cc-cvv-error',
      e.target.value,
      valid,
      getStringMessage('invalid_cvv'),
    );

    this.updateCurrentContext({
      cvvValid: valid,
      cvv,
    });
  };

  enableCheckoutLink = (e) => {
    // Dont wait for focusOut/Blur of CVV field for validations,
    // We need to enable checkout link as soon as user has 3 characters in CVV.
    this.cvvValidations(e);
  };

  handleCardCvvChange = (event, handler) => {
    this.labelEffect(event, handler);
    this.cvvValidations(event);
  };

  validateBeforePlaceOrder = () => {
    const {
      number,
      expiry,
      cvv,
      numberValid,
      expiryValid,
      cvvValid,
      selectedCard,
      tokenizedCard,
    } = this.context;

    if (selectedCard === 'new' && !(numberValid && expiryValid && cvvValid)) {
      return false;
    }

    if (selectedCard === 'existing' && !cvvValid) {
      return false;
    }

    showFullScreenLoader();

    if (selectedCard === 'existing') {
      this.handleCheckoutResponse({ cvv: !cvv ? '' : encodeURIComponent(window.btoa(cvv)), id: tokenizedCard });
    } else {
      const ccInfo = {
        type: 'card',
        number,
        expiry_month: expiry.split('/')[0],
        expiry_year: expiry.split('/')[1],
        cvv,
      };

      const { apiUrl, publicKey } = drupalSettings.checkoutComUpapi;

      axios.post(apiUrl, ccInfo, {
        headers: {
          Authorization: publicKey,
        },
      }).then((response) => {
        this.handleCheckoutResponse(response.data);
      }).catch((error) => {
        // In case of 4 digit CVV code failure.
        if (error.response.status === 422) {
          dispatchCustomEvent('spcCheckoutMessageUpdate', {
            type: 'error',
            message: Drupal.t('Transaction Failed: Invalid CVV'),
          });
        }
        removeFullScreenLoader();
        Drupal.logJavascriptError('Checkout.com UPAPI Token', error.message, GTM_CONSTANTS.PAYMENT_ERRORS);
      });
    }

    return false;
  };

  handleCheckoutResponse = (data) => {
    // Do not process when data has type error.
    if (data.type !== undefined && data.type === 'error') {
      removeFullScreenLoader();
      return;
    }
    // Set selected payment method for GTM push.
    let cardType;
    if (data.card_type !== undefined && data.scheme !== undefined) {
      cardType = ` ${data.card_type} - ${data.scheme}`;
    } else {
      cardType = drupalSettings.payment_methods.checkout_com_upapi !== undefined
        ? drupalSettings.payment_methods.checkout_com_upapi.gtm_name
        : 'Credit / Debit Card';
    }
    // Dispatch the event to push into dataLayer for
    // checkoutOut upapi payment method
    dispatchCustomEvent('orderPaymentMethod', {
      payment_method: (data.card_category === 'Commercial') ? 'MADA' : cardType,
    });
    const { selectedCard } = this.context;
    const { finalisePayment } = this.props;

    // If saved card for topup is false set this as 0.
    let saveCard = 0;
    if (allowSavedCcForTopUp()) {
      // Set udf3 again here to send it for api request.
      saveCard = (selectedCard === 'new' && drupalSettings.user.uid > 0 && document.getElementById('payment-card-save').checked)
        ? 1
        : 0;
    }

    const paymentData = {
      payment: {
        method: 'checkout_com_upapi',
        additional_data: { ...data, save_card: saveCard, card_type: selectedCard },
      },
    };

    finalisePayment(paymentData);
  };

  onExistingCardSelect = (cardHash) => {
    const cvvValid = !(drupalSettings.checkoutComUpapi.cvvCheck === true);

    dispatchCustomEvent('closeModal', 'creditCardList');
    this.updateCurrentContext({
      selectedCard: 'existing',
      tokenizedCard: cardHash,
      cvvValid,
    });
  };

  updateCurrentContext = (obj) => {
    const { updateState } = this.context;
    updateState(obj);
    if (({}).hasOwnProperty.call(obj, 'selectedCard')) {
      Drupal.addItemInLocalStorage(
        'spc_selected_card',
        obj.selectedCard === 'new' ? 'new' : obj.tokenizedCard,
      );
    }

    dispatchCustomEvent('refreshCompletePurchaseSection', {});
  };

  changeCurrentCard = (type) => {
    this.updateCurrentContext({
      selectedCard: type,
    });
  };

  openNewCard = () => {
    dispatchCustomEvent('closeModal', 'creditCardList');
    this.changeCurrentCard('new');
  };

  render() {
    const { selectedCard, tokenizedCard, tokenizedCards } = this.context;
    let activeCard = {};
    if (tokenizedCard !== '') {
      activeCard = tokenizedCards[tokenizedCard];
    }

    const newCard = (
      <NewCard
        labelEffect={this.labelEffect}
        enableCheckoutLink={this.enableCheckoutLink}
        handleCardCvvChange={this.handleCardCvvChange}
      />
    );

    return (
      <>
        <ConditionalView condition={(selectedCard === 'new' && tokenizedCard === '') || allowSavedCcForTopUp() === false}>
          {newCard}
        </ConditionalView>
        <ConditionalView condition={tokenizedCard !== '' && allowSavedCcForTopUp()}>
          <WithModal modalStatusKey="creditCardList">
            {({ triggerOpenModal, triggerCloseModal, isModalOpen }) => (
              <>
                <div className={`spc-checkout-card-option ${selectedCard === 'existing' ? 'selected' : ''}`}>
                  <SelectedCard
                    cardInfo={activeCard}
                    openSavedCardListModal={() => triggerOpenModal()}
                    labelEffect={this.labelEffect}
                    handleCardCvvChange={this.handleCardCvvChange}
                    onExistingCardSelect={this.onExistingCardSelect}
                    selected={selectedCard === 'existing'}
                  />
                </div>
                <div className={`spc-checkout-card-option spc-checkout-card-option-new-card ${selectedCard === 'new' ? 'selected' : ''}`}>
                  <span className="spc-checkout-card-new-card-label" onClick={() => this.changeCurrentCard('new')}>
                    {Drupal.t('New Card')}
                  </span>
                  <ConditionalView condition={selectedCard === 'new'}>
                    {newCard}
                  </ConditionalView>
                </div>
                <Popup
                  className="spc-saved-payment-card-list"
                  open={isModalOpen}
                  closeOnDocumentClick={false}
                  closeOnEscape={false}
                >
                  <>
                    <SavedCardsList
                      selected={selectedCard === 'new' ? '' : tokenizedCard}
                      closeSavedCardListModal={triggerCloseModal}
                      onExistingCardSelect={this.onExistingCardSelect}
                      onNewCardClick={this.openNewCard}
                      tokenizedCards={tokenizedCards}
                    />
                  </>
                </Popup>
              </>
            )}
          </WithModal>
        </ConditionalView>
      </>
    );
  }
}

export default PaymentMethodCheckoutComUpapi;
