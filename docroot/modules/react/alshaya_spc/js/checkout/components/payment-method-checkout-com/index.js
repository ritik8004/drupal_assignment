import React from 'react';
import Popup from 'reactjs-popup';
import { showFullScreenLoader } from '../../../utilities/checkout_util';
import ConditionalView from '../../../common/components/conditional-view';
import SavedCardsList from './components/SavedCardsList';
import AddNewCard from './components/AddNewCard';
import { CheckoutComContext } from '../../../context/CheckoutCom';
import SelectedCard from './components/SelectedCard';

class PaymentMethodCheckoutCom extends React.Component {
  static contextType = CheckoutComContext;

  constructor(props) {
    super(props);
    this.ccCvv = React.createRef();

    this.state = {
      openStoreListModal: false,
    };
  }

  openStoreListModal = () => {
    this.setState({
      openStoreListModal: true,
    });
  }

  closeStoreListModal = () => {
    this.setState({
      openStoreListModal: false,
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
    } = this.context;

    if (!(numberValid && expiryValid && cvvValid)) {
      console.error('Client side validation failed for credit card info');
      throw 'UnexpectedValueException';
    }
    else if (window.CheckoutKit === undefined) {
      console.error('Checkout kit not loaded');
      throw 500;
    }

    showFullScreenLoader();

    if (selectedCard === 'existing') {

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

    const paymentData = {
      payment: {
        method: 'checkout_com',
        additional_data: { ...data },
      },
    };

    console.log(paymentData);
    return;

    this.props.finalisePayment(paymentData);
  };

  render() {
    const { openStoreListModal } = this.state;
    const { selectedCard, tokenizedCard } = this.context;

    let activeCard = [];
    if (tokenizedCard !== '') {
      activeCard = { ...drupalSettings.checkoutCom.tokenizedCards }[tokenizedCard];
    }

    const addNewCard = (
      <AddNewCard
        labelEffect={this.labelEffect}
        handleCardCvvChange={this.handleCardCvvChange}
      />
    );

    return (
      <>
        <ConditionalView condition={selectedCard === 'new' && tokenizedCard === ''}>
          {addNewCard}
        </ConditionalView>
        <ConditionalView condition={tokenizedCard !== ''}>
          <li>
            <SelectedCard
              cardInfo={activeCard}
              openStoreListModal={this.openStoreListModal}
              labelEffect={this.labelEffect}
              handleCardCvvChange={this.handleCardCvvChange}
              onExistingCardSelect={this.onExistingCardSelect}
              selected={selectedCard === 'existing'}
            />
          </li>
          <li>
            <span onClick={() => this.changeCurrentCard('new')}>{Drupal.t('new card')}</span>
            <ConditionalView condition={selectedCard === 'new'}>
              {addNewCard}
            </ConditionalView>
          </li>
        </ConditionalView>
        <Popup open={openStoreListModal} onClose={this.closeStoreListModal} closeOnDocumentClick={false}>
          <>
            <SavedCardsList
              selected={tokenizedCard}
              closeStoreListModal={this.closeStoreListModal}
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
