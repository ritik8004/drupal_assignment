import React from 'react';
import Popup from 'reactjs-popup';
import { showFullScreenLoader } from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import SavedCardsList from './components/SavedCardsList';
import NewCard from './components/NewCard';
import { CheckoutComContext } from '../../../context/CheckoutCom';
import SelectedCard from './components/SelectedCard';
import Cookies from 'js-cookie';

class PaymentMethodCheckoutCom extends React.Component {
  static contextType = CheckoutComContext;

  constructor(props) {
    super(props);
    this.ccCvv = React.createRef();

    this.state = {
      openSavedCardListModal: false,
    };
  }

  componentDidMount() {
    const { tokenizedCard } = this.context;
    let activeCard = {};
    if (tokenizedCard !== '') {
      activeCard = { ...drupalSettings.checkoutCom.tokenizedCards }[tokenizedCard];
    }

    this.updateCurrentContext({
      cvvValid: !(activeCard.mada === true || drupalSettings.checkoutCom.Enforce3d === true),
    });
  }

  openSavedCardListModal = () => {
    this.setState({
      openSavedCardListModal: true,
    });
  }

  closeSavedCardListModal = () => {
    this.setState({
      openSavedCardListModal: false,
    });
  }

  labelEffect = (e, handler) => {
    if (handler === 'blur') {
      if (e.currentTarget.value.length > 0) {
        e.currentTarget.classList.add('focus');
      } else {
        e.currentTarget.classList.remove('focus');
      }
    }
  }

  handleCardCvvChange = (event) => {
    if (window.CheckoutKit === undefined) {
      console.error('CheckoutKit not available');
      throw 500;
    }

    const cvv = parseInt(event.target.value);
    const valid = (cvv >= 100 && cvv <= 9999);

    this.updateCurrentContext({
      cvvValid: valid,
      cvv,
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
      console.error('Client side validation failed for credit card info');
      throw 'UnexpectedValueException';
    } else if (window.CheckoutKit === undefined) {
      console.error('Checkout kit not loaded');
      throw 500;
    }

    if (selectedCard === 'existing' && !cvvValid) {
      console.error('Client side validation failed for credit card info');
      throw 'UnexpectedValueException';
    }

    showFullScreenLoader();

    if (selectedCard === 'existing') {
      this.handleCheckoutResponse({ cvv: cvv.length > 0 ? encodeURIComponent(window.btoa(cvv)) : '', id: tokenizedCard });
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

    // Throwing 200 error, we want to handle place order in custom way.
    throw 200;
  }

  handleCheckoutResponse = (data) => {
    // @TODO: Handle errors.
    const { selectedCard } = this.context;

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

    this.props.finalisePayment(paymentData);
  };

  onExistingCardSelect = (cardHash, madaCard) => {
    const cvvValid = !(madaCard === true || drupalSettings.checkoutCom.Enforce3d === true);

    this.closeSavedCardListModal();
    this.updateCurrentContext({
      selectedCard: 'existing',
      tokenizedCard: cardHash,
      cvvValid,
    });
  }

  updateCurrentContext = (obj) => {
    const { updateState } = this.context;
    updateState(obj);
    if (({}).hasOwnProperty.call(obj, 'selectedCard')) {
      Cookies.set('spc_selected_card', obj.selectedCard === 'new' ? 'new' : obj.tokenizedCard);
    }
  }

  changeCurrentCard = (type) => {
    this.updateCurrentContext({
      selectedCard: type,
    });
  }

  openNewCard = () => {
    this.closeSavedCardListModal();
    this.changeCurrentCard('new');
  }

  render() {
    const { openSavedCardListModal } = this.state;
    const { selectedCard, tokenizedCard } = this.context;

    let activeCard = {};
    if (tokenizedCard !== '') {
      activeCard = { ...drupalSettings.checkoutCom.tokenizedCards }[tokenizedCard];
    }

    const newCard = (
      <NewCard
        labelEffect={this.labelEffect}
        handleCardCvvChange={this.handleCardCvvChange}
      />
    );

    return (
      <>
        <ConditionalView condition={selectedCard === 'new' && tokenizedCard === ''}>
          {newCard}
        </ConditionalView>
        <ConditionalView condition={tokenizedCard !== ''}>
          <div className={`spc-checkout-card-option ${selectedCard === 'existing' ? 'selected' : ''}`}>
            <SelectedCard
              cardInfo={activeCard}
              openSavedCardListModal={this.openSavedCardListModal}
              labelEffect={this.labelEffect}
              handleCardCvvChange={this.handleCardCvvChange}
              onExistingCardSelect={this.onExistingCardSelect}
              selected={selectedCard === 'existing'}
            />
          </div>
          <div className={`spc-checkout-card-option spc-checkout-card-option-new-card ${selectedCard === 'new' ? 'selected' : ''}`}>
            <span className="spc-checkout-card-new-card-label" onClick={() => this.changeCurrentCard('new')}>
              {Drupal.t('new card')}
            </span>
            <ConditionalView condition={selectedCard === 'new'}>
              {newCard}
            </ConditionalView>
          </div>
        </ConditionalView>
        <Popup className="spc-saved-payment-card-list" open={openSavedCardListModal} onClose={this.closeSavedCardListModal} closeOnDocumentClick={false}>
          <>
            <SavedCardsList
              selected={selectedCard === 'new' ? '' : tokenizedCard}
              closeSavedCardListModal={this.closeSavedCardListModal}
              onExistingCardSelect={this.onExistingCardSelect}
              onNewCardClick={this.openNewCard}
            />
          </>
        </Popup>
      </>
    );
  }
}

export default PaymentMethodCheckoutCom;
