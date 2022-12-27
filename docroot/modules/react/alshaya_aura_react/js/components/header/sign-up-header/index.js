import React from 'react';
import parse from 'html-react-parser';
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

    if (document.querySelector('.block-alshaya-main-menu')) {
      document.querySelector('.block-alshaya-main-menu').classList.add('aura-header-modal-open');
    }
  };

  closeOTPModal = () => {
    this.setState({
      isOTPModalOpen: false,
    });

    if (document.querySelector('.block-alshaya-main-menu')) {
      document.querySelector('.block-alshaya-main-menu').classList.remove('aura-header-modal-open');
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
                {Drupal.t('Say hello to')}
                <div className="aura-header-icon">
                  <AuraHeaderIcon />
                </div>
                <button type="button" className="close" onClick={() => this.closeHeaderModal()} />
              </div>
            </div>
            <div className="aura-popup-body">
              <span>
                {parse(Drupal.t('Earn and spend points when you shop at your favourite brands and unlock exclusive benefits. <a href="@know_more_link" target="_blank" rel="noopener noreferrer">Learn more</a>',
                  {
                    '@know_more_link': Drupal.url(headerLearnMoreLink),
                  },
                  {
                    context: 'aura',
                  }))}
              </span>
            </div>
            <div className="aura-popup-footer">
              <div
                className="join-aura"
                onClick={() => {
                  this.openOTPModal();
                  Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_UP', label: 'initiated' });
                }}
              >
                {Drupal.t('Join Aura', {}, {
                  context: 'aura',
                })}
              </div>
              <div className="already-member">
                <span className="already-member__text">{Drupal.t('Already a member?', {}, { context: 'aura' })}</span>
                <span className="already-member__text">{Drupal.t('Add your account details at shopping bag.', {}, { context: 'aura' })}</span>
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
