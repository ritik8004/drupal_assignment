import React from 'react';
import Popup from 'reactjs-popup';
import { addPaymentMethodInCart } from '../../../utilities/update_cart';
import {
  placeOrder,
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../utilities/checkout_util';
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

    addPaymentMethodInCart('finalise payment', paymentData).then((result) => {
      if (result.error !== undefined && result.error) {
        removeFullScreenLoader();
        console.error(result.error);
        return;
      }

      // 2D flow success.
      if (result.cart_id !== undefined && result.cart_id) {
        const { cart } = this.props;
        placeOrder(cart.cart.cart_id, cart.selected_payment_method);
      } else if (result.success === undefined || !(result.success)) {
        // 3D flow error.
        console.error(result);
      } else if (result.redirectUrl !== undefined) {
        // 3D flow success.
        window.location = result.redirectUrl;
      } else {
        console.error(result);
        removeFullScreenLoader();
      }
    }).catch((error) => {
      removeFullScreenLoader();
      console.error(error);
    });
  }

  onExistingCardSelect = (cardHash) => {
    this.closeStoreListModal();
    this.updateCurrentContext({
      selectedCard: 'existing',
      tokenizedCard: cardHash,
    });
  }

  updateCurrentContext = (obj) => {
    const { updateState } = this.context;
    updateState(obj);
  }

  changeCurrentCard = (type) => {
    this.updateCurrentContext({
      selectedCard: type,
    });
  }

  openNewCard = () => {
    this.closeStoreListModal();
    this.changeCurrentCard('new');
    if (window.innerWidth < 768) {
      // Code here to navigate to nwe card form.
    }
  }

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
