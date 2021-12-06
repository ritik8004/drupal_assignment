import React from 'react';
import { callMagentoApi } from '../../../../../js/utilities/requestHelper';
import getCurrencyCode from '../../../../../js/utilities/util';
import logger from '../../../../../js/utilities/logger';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import EditEgiftCardAmount from '../EditEgiftCardAmount';

class PaymentMethodLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
      exceedingAmount: 0,
      egiftcardbalance: 0,
      apiCallFlag: false, // Set when API call is complete.
      setChecked: false,
      egiftcardvalidity: '',
    };
  }

  async componentDidMount() {
    const params = { 'accountInfo': { 'cardNumber': 4250120656063430, 'email': 'm.test@gmail.com'} };
    const response = await callMagentoApi('/V1/egiftcard/getBalance', 'POST', params);
    if (typeof response.data !== 'undefined' && typeof response.data.error === 'undefined') {
      this.setState({
        egiftcardbalance: response.data.items.current_balance,
        egiftcardvalidity: response.data.items.expiry_date,
      });
    }
    this.setState({
      apiCallFlag: true,
    });
    this.setState({
      // @todo get amount and update the state.
      egiftcardbalance: 2,
    });
  }

  handleCheckboxChange = (e) => this.setState(
    { setChecked: e.target.checked }
  );

  openModal = (e) => {
    console.log('open');
    this.setState({
      open: true,
    });

    e.stopPropagation();
  };

  closeModal = () => {
    console.log('close');
    this.setState({
      open: false,
    });
  };

  // Update egift amount.
  handleExceedingAmount = (Amt) => {
    if (Amt > 0){
      this.setState({
        exceedingAmount: Amt,
      });
    }
  }

  render() {
    const { open, egiftcardbalance, setChecked, exceedingAmount } = this.state;
    const { cart } = this.props;

    return (
      <>
        <div className="egift-card-payment">
          <input type="checkbox" id="link-egift-card" onChange={this.handleCheckboxChange} checked={setChecked} />
          <label htmlFor="egift-link-card">
            {
              Drupal.t('Pay using egift card (Available Balance: @currencyCode @amount)',
                {
                  '@currencyCode': getCurrencyCode(), '@amount': egiftcardbalance,
                }, { context: 'egift' })
            }
          </label>
          <ConditionalView condition={setChecked === true && egiftcardbalance > 0}>
            <ConditionalView conditional={open}>
              <EditEgiftCardAmount
                closeModal={this.closeModal}
                open={open}
                amount={egiftcardbalance}
                handleExceedingAmount={this.handleExceedingAmount}
                cartTotal={cart}
              />
            </ConditionalView>
            <ConditionalView condition={exceedingAmount > 0}>
              <div id="egift_linkcard_message" className="exceed-message">
                {
                  Drupal.t('Pay @currencyCode @amount using another payment method to complete purchase',
                    {
                      '@currencyCode': getCurrencyCode(), '@amount': exceedingAmount,
                    }, { context: 'egift' })
                }
              </div>
            </ConditionalView>
            <div onClick={this.openModal}><strong>{Drupal.t('Edit amount to use', {}, { context: 'egift' })}</strong></div>
          </ConditionalView>
          <ConditionalView condition={setChecked === true && egiftcardbalance === 0}>
            <div id="egift_linkcard_message" className="exceed-message">
              {
                Drupal.t('Linked card has 0 Balance please use another payment method to complete purchase', {}, { context: 'egift' })
              }
            </div>
          </ConditionalView>
        </div>
      </>
    );
  }
}

export default PaymentMethodLinkedCard;
