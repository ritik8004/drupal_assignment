import React from 'react';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { getAuraConfig } from '../../../utilities/helper';
import SignUpOtpModal from '../sign-up-otp-modal';

class SignUpHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
    };
  }

  openOTPModal = () => {
    const { openHeaderModal } = this.props;
    openHeaderModal();
    this.setState({
      isOTPModalOpen: true,
    });

    if (document.getElementById('block-alshayamainmenu')) {
      document.getElementById('block-alshayamainmenu').classList.add('aura-header-modal-open');
    }
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });

    if (document.getElementById('block-alshayamainmenu')) {
      document.getElementById('block-alshayamainmenu').classList.remove('aura-header-modal-open');
    }
  };

  render() {
    const {
      isOTPModalOpen,
    } = this.state;

    const {
      isHeaderModalOpen,
      isNotExpandable,
    } = this.props;

    const { headerLearnMoreLink } = getAuraConfig();

    return (
      <>
        <ConditionalView condition={isHeaderModalOpen && !isNotExpandable}>
          <div className="aura-header-popup-wrapper">
            <div className="aura-popup-sub-header">
              <h3>{Drupal.t('Say hello to Aura')}</h3>
            </div>
            <div className="aura-popup-header">
              <div className="title title--one">
                {Drupal.t('Bespoke rewards.')}
              </div>
              <div className="title title--two">
                {Drupal.t('Personalised for you.')}
              </div>
            </div>
            <div className="aura-popup-body">
              <p className="desktop-only">{Drupal.t('Good things come to those with taste. Aura is the new loyalty programme rewarding you for spending in the places you love while unlocking exclusive access to unrivalled experiences.')}</p>
              <a
                href={Drupal.url(headerLearnMoreLink)}
                target="_blank"
                rel="noopener noreferrer"
                className="learn-more"
              >
                {Drupal.t('Learn more')}
              </a>
            </div>
            <div className="aura-popup-footer">
              <div
                className="join-aura"
                onClick={() => this.openOTPModal()}
              >
                {Drupal.t('Join now')}
              </div>
              <div className="aura-popup-footer-bottom">
                <p>{Drupal.t('Already a member? Add your account details at checkout.')}</p>
              </div>
            </div>
          </div>
        </ConditionalView>
        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
        />
      </>
    );
  }
}

export default SignUpHeader;
