import React from 'react';
import CheckoutMessage from '../../utilities/checkout-message';
import SASharePopup from './s-a-share-popup';
import SAIcons from '../s-a-icons';
import SALogo from '../s-a-icons/s-a-logo';

class SAShareStrip extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      modalOpen: false,
      shareByContext: null,
    };
  }

  componentDidMount() {
    document.addEventListener('smartAgentClosePopup', this.smartAgentClosePopup, false);
  }

  componentWillUnmount() {
    document.removeEventListener('smartAgentClosePopup', this.smartAgentClosePopup, false);
  }

  /**
   * Close popup.
   *
   * @param event
   */
  smartAgentClosePopup = () => {
    this.closeModal();
  };

  openModal = (context) => {
    this.setState({
      modalOpen: true,
      shareByContext: context,
    });
  };

  closeModal = () => {
    this.setState({
      modalOpen: false,
    });
  };

  render() {
    const {
      modalOpen,
      shareByContext,
    } = this.state;

    return (
      <CheckoutMessage
        type="smart-agent-share"
        context="smart-agent-share"
      >
        <SALogo />
        <span className="message">{`${Drupal.t('Share basket with customer via')}`}</span>
        <div className="share-options">
          <span className="share-option wa" onClick={() => this.openModal('wa')}>
            <SAIcons service="wa" />
            <span className="label">{Drupal.t('WhatsApp')}</span>
          </span>
          <span className="share-option email" onClick={() => this.openModal('email')}>
            <SAIcons service="email" />
            <span className="label">{Drupal.t('Email')}</span>
          </span>
          <span className="share-option sms" onClick={() => this.openModal('sms')}>
            <SAIcons service="sms" />
            <span className="label">{Drupal.t('SMS')}</span>
          </span>
        </div>
        <SASharePopup
          modalOpen={modalOpen}
          shareByContext={shareByContext}
          closeModal={this.closeModal}
        />
      </CheckoutMessage>
    );
  }
}

export default SAShareStrip;
