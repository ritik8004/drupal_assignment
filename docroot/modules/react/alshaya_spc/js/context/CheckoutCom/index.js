import React from 'react';
import Cookies from 'js-cookie';

export const CheckoutComContext = React.createContext();

class CheckoutComContextProvider extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      cvc: '',
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

    let selectedCard = (hasCards) ? 'existing' : 'new';
    selectedCard = (hasCards && Cookies.get('spc_selected_card') && Cookies.get('spc_selected_card') === 'new')
      ? 'new'
      : selectedCard;

    let tokenizedCard = (hasCards) ? Object.keys({ ...drupalSettings.checkoutCom.tokenizedCards })[0] : '';
    tokenizedCard = (tokenizedCard !== '' && Cookies.get('spc_selected_card') && Cookies.get('spc_selected_card') !== 'new')
      ? Cookies.get('spc_selected_card')
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
