import React from 'react';
import Popup from 'reactjs-popup';
import ConditionalView from '../../../../../js/utilities/components/conditional-view';
import { getAuraConfig } from '../../../utilities/helper';
import SignUpOtpModal from '../sign-up-otp-modal';
import AuraHeaderIcon from '../../../svg-component/aura-header-icon';
import AuraFormLinkCardOTPModal
  from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';

class SignUpHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isOTPModalOpen: false,
      isLinkCardModalOpen: false,
      chosenCountryCode: null,
    };
  }

  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

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

  openLinkCardModal = () => {
    const { openHeaderModal } = this.props;
    openHeaderModal();
    this.setState({
      isLinkCardModalOpen: true,
    });
  };

  closeLinkCardModal = () => {
    this.setState({
      isLinkCardModalOpen: false,
    });
  };

  closeHeaderModal = () => {
    const { openHeaderModal } = this.props;
    openHeaderModal();
  };

  render() {
    const {
      isOTPModalOpen,
      isLinkCardModalOpen,
      chosenCountryCode,
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
            <div className="aura-popup-header">
              <div className="desktop-only">
                <div className="aura-header-icon">
                  <AuraHeaderIcon />
                </div>
                <button type="button" className="close" onClick={() => this.closeHeaderModal()} />
              </div>
            </div>
            <div className="aura-popup-body">
              <span>
                {Drupal.t('To earn and spend points while you shop and enjoy exclusive benefits.')}
              </span>
              <span className="learn-more-wrapper">
                <a
                  href={Drupal.url(headerLearnMoreLink)}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="learn-more"
                >
                  {Drupal.t('Know more')}
                </a>
              </span>
              <span>
                {Drupal.t('about Aura.')}
              </span>
            </div>
            <div className="aura-popup-footer">
              <div
                className="join-aura"
                onClick={() => this.openOTPModal()}
              >
                {Drupal.t('Join now')}
              </div>
            </div>
          </div>
        </ConditionalView>
        <SignUpOtpModal
          isOTPModalOpen={isOTPModalOpen}
          closeOTPModal={this.closeOTPModal}
        />
        <Popup
          className="aura-modal-form link-card-otp-modal"
          open={isLinkCardModalOpen}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormLinkCardOTPModal
            closeLinkCardOTPModal={() => this.closeLinkCardModal()}
            setChosenCountryCode={this.setChosenCountryCode}
            chosenCountryCode={chosenCountryCode}
          />
        </Popup>
      </>
    );
  }
}

export default SignUpHeader;
