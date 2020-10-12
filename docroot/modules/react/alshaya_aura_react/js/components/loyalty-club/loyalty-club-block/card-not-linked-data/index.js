import React from 'react';
import Cleave from 'cleave.js/react';
import { getAPIData } from '../../../../utilities/api/fetchApiData';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraLogo from '../../../../svg-component/aura-logo';

export default class AuraMyAccountOldCardFound extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      cardNumber: '',
    };
  }

  componentDidMount() {
    // API call to get card number with logged in user's email id.
    const apiUrl = 'get/loyalty-club/get-apc-user-details-by-email';
    const apiData = getAPIData(apiUrl);

    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.data !== undefined && result.data.error === undefined) {
          this.setState({
            cardNumber: result.data.apcCard,
          });
        }
      });
    }
  }

  handleNotYou = () => {
    const { cardNumber } = this.state;
    const { handleNotYou } = this.props;
    handleNotYou(cardNumber);
  }

  render() {
    const { cardNumber } = this.state;

    return (
      <div className="aura-myaccount-no-linked-card-wrapper old-card-found">
        <div className="aura-logo">
          <ConditionalView condition={window.innerWidth > 1024}>
            <AuraLogo stacked="vertical" />
          </ConditionalView>
          <ConditionalView condition={window.innerWidth < 1025}>
            <AuraLogo stacked="horizontal" />
          </ConditionalView>
        </div>
        <div className="aura-myaccount-no-linked-card-description old-card-found">
          <div className="header">
            { Drupal.t('An Aura loyalty card is associate with your email address. It just a takes one click to link.') }
            <span className="bold">{Drupal.t('Do you want to link now?')}</span>
          </div>
          <div className="card-number-wrapper">
            <Cleave
              name="aura-my-account-link-card"
              className="aura-my-account-link-card"
              disabled
              value={cardNumber}
              options={{ blocks: [4, 4, 4, 4] }}
            />
            <a
              className="link-your-card"
            >
              { Drupal.t('Link your card') }
            </a>
            <a
              className="not-you"
              onClick={this.handleNotYou}
            >
              { Drupal.t('Not you?') }
            </a>
          </div>
        </div>
      </div>
    );
  }
}
