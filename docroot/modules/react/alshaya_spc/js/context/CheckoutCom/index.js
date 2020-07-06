import React from 'react';
import { getStorageInfo } from '../../utilities/storage';

export const CheckoutComContext = React.createContext();

class CheckoutComContextProvider extends React.Component {
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
    const storageSelectedCard = getStorageInfo('spc_selected_card');
    let selectedCard = (hasCards) ? 'existing' : 'new';
    selectedCard = (hasCards && storageSelectedCard && storageSelectedCard === 'new')
      ? 'new'
      : selectedCard;

    let tokenizedCard = (hasCards) ? Object.keys({ ...drupalSettings.checkoutCom.tokenizedCards })[0] : '';
    tokenizedCard = (tokenizedCard !== '' && storageSelectedCard && storageSelectedCard !== 'new')
      ? storageSelectedCard
      : tokenizedCard;

    this.setState((prevState) => ({
      ...prevState,
      selectedCard,
      tokenizedCard,
    }));
  }

  hasTokenizedCards = () => (
    drupalSettings.user.uid > 0 && Object.keys(drupalSettings.checkoutCom.tokenizedCards).length > 0
  );

  updateState = (newState) => {
    this.setState((prevState) => ({
      ...prevState,
      ...newState,
    }));
  }

  render() {
    const { children } = this.props;

    return (
      <CheckoutComContext.Provider
        value={
          {
            ...this.state,
            updateState: this.updateState,
          }
        }
      >
        { children }
      </CheckoutComContext.Provider>
    );
  }
}

export default CheckoutComContextProvider;
