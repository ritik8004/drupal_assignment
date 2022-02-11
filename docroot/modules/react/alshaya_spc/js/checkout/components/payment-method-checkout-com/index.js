import React from 'react';
import Popup from 'reactjs-popup';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
  validateCvv,
} from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import SavedCardsList from './components/SavedCardsList';
import NewCard from './components/NewCard';
import { CheckoutComContext } from '../../../context/CheckoutCom';
import SelectedCard from './components/SelectedCard';
import dispatchCustomEvent from '../../../utilities/events';
import getStringMessage from '../../../utilities/strings';
import { handleValidationMessage } from '../../../utilities/form_item_helper';
import WithModal from '../with-modal';

class PaymentMethodCheckoutCom extends React.Component {
  static contextType = CheckoutComContext;

  componentDidMount() {
    const { tokenizedCard } = this.context;
    let activeCard = {};
    if (tokenizedCard !== '') {
      activeCard = { ...drupalSettings.checkoutCom.tokenizedCards }[tokenizedCard];
    }

    this.updateCurrentContext({
      cvvValid: !(activeCard.mada === true || drupalSettings.checkoutCom.enforce3d === true),
    });

    dispatchCustomEvent('refreshCompletePurchaseSection', {});
    // Handle api error which triggered on card tokenisation fail.
    window.CheckoutKit.addEventHandler(
      window.CheckoutKit.Events.API_ERROR,
      (event) => this.handleCheckoutKitJsErrors(event.data),
    );
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
    if (window.CheckoutKit === undefined) {
      Drupal.logJavascriptError('CheckoutKit not available', '', GTM_CONSTANTS.PAYMENT_ERRORS);
      return;
    }
    this.labelEffect(event, handler);
    this.cvvValidations(event);
  };

  handleCheckoutKitJsErrors = (data) => {
    Drupal.logJavascriptError(
      'Payment failed',
      `Payment failed with error code ${data.errorCode}`,
      GTM_CONSTANTS.GENUINE_PAYMENT_ERRORS,
    );
    dispatchCustomEvent('spcCheckoutMessageUpdate', {
      type: 'error',
      message: (data.errorCode === '70000')
        ? getStringMessage('transaction_failed')
        : getStringMessage('payment_error'),
    });
  }

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

    if (window.CheckoutKit === undefined) {
      Drupal.logJavascriptError('Checkout kit not loaded', '', GTM_CONSTANTS.PAYMENT_ERRORS);

      dispatchCustomEvent('spcCheckoutMessageUpdate', {
        type: 'error',
        message: getStringMessage('payment_error'),
      });

      return false;
    }

    showFullScreenLoader();

    if (selectedCard === 'existing') {
      this.handleCheckoutResponse({ cvv: !cvv ? '' : encodeURIComponent(window.btoa(cvv)), id: tokenizedCard });
    } else {
      const udf3 = (drupalSettings.user.uid > 0 && document.getElementById('payment-card-save').checked)
        ? 'storeInVaultOnSuccess'
        : '';

      const ccInfo = {
        number,
        expiryMonth: expiry.split('/')[0],
        expiryYear: expiry.split('/')[1],
        cvv,
        udf3,
      };

      window.CheckoutKit.configure({
        debugMode: drupalSettings.checkoutCom.debugMode,
        publicKey: drupalSettings.checkoutCom.publicKey,
      });

      window.CheckoutKit.createCardToken(ccInfo, this.handleCheckoutResponse);
    }

    return false;
  };

  handleCheckoutResponse = (data) => {
    // Do not process when data has type error.
    if (data.type === 'error') {
      removeFullScreenLoader();
      return;
    }

    if (data.card.paymentMethod !== undefined) {
      drupalSettings.payment_methods.checkout_com.gtm_name = data.card.paymentMethod;
    }

    const { selectedCard } = this.context;
    const { finalisePayment } = this.props;

    // Set udf3 again here to send it for api request.
    const udf3 = (selectedCard === 'new' && drupalSettings.user.uid > 0 && document.getElementById('payment-card-save').checked)
      ? 'storeInVaultOnSuccess'
      : '';

    const paymentData = {
      payment: {
        method: 'checkout_com',
        additional_data: { ...data, udf3, card_type: selectedCard },
      },
    };
    finalisePayment(paymentData);
  };

  onExistingCardSelect = (cardHash, madaCard) => {
    const cvvValid = !(madaCard === true || drupalSettings.checkoutCom.enforce3d === true);

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
    const { selectedCard, tokenizedCard } = this.context;

    let activeCard = {};
    if (tokenizedCard !== '') {
      activeCard = { ...drupalSettings.checkoutCom.tokenizedCards }[tokenizedCard];
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
        <ConditionalView condition={selectedCard === 'new' && tokenizedCard === ''}>
          {newCard}
        </ConditionalView>
        <ConditionalView condition={tokenizedCard !== ''}>
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

export default PaymentMethodCheckoutCom;
