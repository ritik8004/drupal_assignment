import React from 'react';
import { allowSavedCcForTopUp } from '../../utilities/egift_util';

export const CheckoutComUpapiContext = React.createContext();

class CheckoutComUpapiContextProvider extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cvv: '',
      expiry: '',
      number: '',
      cardType: '',
      tokenizedCard: '',
      selectedCard: 'new',
      numberValid: false,
      expiryValid: false,
      cvvValid: false,
    };
  }

  componentDidMount() {
    const hasCards = this.hasTokenizedCards();
    const storageSelectedCard = Drupal.getItemFromLocalStorage('spc_selected_card');
    let selectedCard = (hasCards) ? 'existing' : 'new';
    selectedCard = (hasCards && storageSelectedCard && storageSelectedCard === 'new')
      ? 'new'
      : selectedCard;

    const { tokenizedCards } = drupalSettings.checkoutComUpapi;
    let tokenizedCard = (hasCards) ? Object.keys(tokenizedCards)[0] : '';
    tokenizedCard = (tokenizedCard !== '' && storageSelectedCard && storageSelectedCard !== 'new')
      ? storageSelectedCard
      : tokenizedCard;

    this.setState((prevState) => ({
      ...prevState,
      selectedCard,
      tokenizedCard,
    }));
  }

  hasTokenizedCards = () => {
    const { tokenize, tokenizedCards } = drupalSettings.checkoutComUpapi;
    return (
      drupalSettings.user.uid > 0
      && tokenize
      && Object.keys(tokenizedCards).length > 0
      // Condition to not use existing card for egift topup.
      && allowSavedCcForTopUp()
    );
  };

  updateState = (newState) => {
    this.setState((prevState) => ({
      ...prevState,
      ...newState,
    }));
  };

  render() {
    const { children } = this.props;

    return (
      <CheckoutComUpapiContext.Provider
        value={
          {
            ...this.state,
            updateState: this.updateState,
          }
        }
      >
        { children }
      </CheckoutComUpapiContext.Provider>
    );
  }
}

export default CheckoutComUpapiContextProvider;
