import React from 'react';
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
        { isHeaderModalOpen && !isNotExpandable
          && (
          <div className="aura-header-popup-wrapper">
            <div className="aura-popup-header">
              <div className="title title--one">
                {Drupal.t('Bespoke rewards.')}
              </div>
              <div className="title title--two">
                {Drupal.t('Bespoke lifestyles.')}
              </div>
            </div>
            <div className="aura-popup-sub-header">
              <h3>{Drupal.t('Say hello to AURA')}</h3>
            </div>
            <div className="aura-popup-body">
              <p>{Drupal.t('Good things come to those with taste. Say hello to AURA, a lifestyle program catered to your taste for refined brands and experiences.')}</p>
            </div>
            <div className="aura-popup-footer">
              <div
                className="join-aura"
                onClick={() => this.openOTPModal()}
              >
                {Drupal.t('Sign up now')}
              </div>
              <a href={headerLearnMoreLink} className="learn-more">
                {Drupal.t('Learn more')}
              </a>
            </div>
          </div>
          )}
        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
        />
      </>
    );
  }
}

export default SignUpHeader;
