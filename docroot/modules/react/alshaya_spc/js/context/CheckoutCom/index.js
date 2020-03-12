import React from 'react';

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

    this.setState((prevState) => ({
      ...prevState,
      selectedCard: !hasCards ? 'new' : 'existing',
      tokenizedCard: hasCards ? Object.keys({ ...drupalSettings.checkoutCom.tokenizedCards })[0] : '',
    }));
  }

  hasTokenizedCards = () => (
    drupalSettings.user.uid > 0 && Object.keys(drupalSettings.checkoutCom.tokenizedCards).length > 0
  );

  updateState = (newStates) => {
    this.setState((prevState) => ({
      ...prevState,
      ...newStates,
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
