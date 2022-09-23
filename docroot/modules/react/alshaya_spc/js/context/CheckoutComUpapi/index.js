import React from 'react';
import { getTokenizedCards } from '../../utilities/checkout_util';
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
      tokenizedCards: [],
    };
  }

  componentDidMount() {
    const { tokenize } = drupalSettings.checkoutComUpapi;
    // Get the tokenized cards only if tokenize if enabled.
    if (tokenize) {
      const tokenizedCards = getTokenizedCards();
      // Show the loader till we have the response.
      if (tokenizedCards instanceof Promise) {
        tokenizedCards.then((result) => {
          let hasCards = false;
          if (Object.keys(result).length > 0) {
            hasCards = this.hasTokenizedCards();
          }

          const storageSelectedCard = Drupal.getItemFromLocalStorage('spc_selected_card');
          let selectedCard = (hasCards) ? 'existing' : 'new';
          selectedCard = (hasCards && storageSelectedCard && storageSelectedCard === 'new')
            ? 'new'
            : selectedCard;

          let tokenizedCard = (hasCards) ? Object.keys(result)[0] : '';
          tokenizedCard = (tokenizedCard !== '' && storageSelectedCard && storageSelectedCard !== 'new')
            ? storageSelectedCard
            : tokenizedCard;

          this.setState((prevState) => ({
            ...prevState,
            selectedCard,
            tokenizedCard,
            tokenizedCards: result,
          }));
        });
      }
    }
  }

  // Condition to not use existing card for egift topup.
  hasTokenizedCards = () => drupalSettings.user.uid > 0 && allowSavedCcForTopUp();

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
