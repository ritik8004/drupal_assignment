import React from 'react';
import Popup from 'reactjs-popup';
import AuraLogo from '../../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../../alshaya_spc/js/common/components/conditional-view';
import AuraFormSignUpOTPModal
  from '../../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';

class AuraMyAccountNoLinkedCard extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
    };
  }

  handleSignUp = () => {
    const { handleSignUp } = this.props;
    handleSignUp();
  }

  openOTPModal = () => {
    this.setState({
      isOTPModalOpen: true,
    });
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });
  };

  render() {
    const { isOTPModalOpen } = this.state;
    const { handleSignUp } = this.props;

    return (
      <div className="aura-myaccount-no-linked-card-wrapper no-card-found fadeInUp">
        <div className="aura-logo">
          <ConditionalView condition={window.innerWidth > 1024}>
            <AuraLogo stacked="vertical" />
          </ConditionalView>
          <ConditionalView condition={window.innerWidth < 1025}>
            <AuraLogo stacked="horizontal" />
          </ConditionalView>
        </div>
        <div className="aura-myaccount-no-linked-card-description no-card-found">
          <div className="link-your-card">
            { Drupal.t('Already AURA Member?') }
            <div className="btn">
              { Drupal.t('Link your card') }
            </div>
          </div>
          <div className="sign-up">
            { Drupal.t('Ready to be Rewarded?') }
            <div
              className="btn"
              onClick={() => this.openOTPModal()}
            >
              { Drupal.t('Sign up') }
            </div>
            <Popup
              className="aura-modal-form otp-modal"
              open={isOTPModalOpen}
              closeOnEscape={false}
              closeOnDocumentClick={false}
            >
              <AuraFormSignUpOTPModal
                closeOTPModal={() => this.closeOTPModal()}
                handleSignUp={handleSignUp}
              />
            </Popup>
          </div>
        </div>
      </div>
    );
  }
}
export default AuraMyAccountNoLinkedCard;
