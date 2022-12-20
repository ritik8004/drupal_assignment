import React from 'react';
import Popup from 'reactjs-popup';
import Cleave from 'cleave.js/react';
import AuraLogo from '../../../svg-component/aura-logo';
import ConditionalView
  from '../../../../../alshaya_spc/js/common/components/conditional-view';
import { getNotYouLabel } from '../../../utilities/aura_utils';
import AuraFormSignUpOTPModal from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-new-aura-user-form';
import AuraFormLinkCardOTPModal from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-link-card-otp-modal-form';
import AuraFormUnlinkedCard from '../../../../../alshaya_spc/js/aura-loyalty/components/aura-forms/aura-unlinked-card';
import {
  getUserDetails,
  getUserProfileInfo,
} from '../../../utilities/helper';
import { hasValue } from '../../../../../js/utilities/conditionsUtility';
import getStringMessage from '../../../../../js/utilities/strings';

class SignUpCompleteHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openLinkOldCardModal: false,
      openOtpModal: false,
      chosenCountryCode: null,
      chosenUserMobile: null,
      openNewUserModal: false,
      openLinkCardModal: false,
      // For the accordion in mobile, default state should be closed. In desktop
      // we don't have an accordion.
      active: false,
    };
  }

  /**
   * Event listener to listen to actions and close the openLinkOldCardModal popup.
   */
  componentDidMount() {
    document.addEventListener('loyaltyStatusUpdated', this.updateState, false);
  }

  /**
   * Remove the event listener when component gets deleted.
   */
  componentWillUnmount() {
    document.removeEventListener('loyaltyStatusUpdated', this.updateState, false);
    // Remove aura-header-modal-open class from the main menu,
    // This class will have no usage once component is unmounted.
    if (document.querySelector('.block-alshaya-main-menu')) {
      document.querySelector('.block-alshaya-main-menu').classList.remove('aura-header-modal-open');
    }
  }

  /**
   * Event listener callback to Close the openLinkOldCardModal popup.
   */
  updateState = () => {
    // Close  Link Old card modal.
    this.setState({
      openLinkOldCardModal: false,
    });
  }

  /**
   * Toggle the status of accordion in mobile.
   */
  handleAccordionStatus = () => {
    const { active } = this.state;
    this.setState({ active: !active });
  };

  /**
   * Set Country code to pass it to new user modal.
   */
  setChosenCountryCode = (code) => {
    this.setState({
      chosenCountryCode: code,
    });
  };

  /**
   * Set Mobile number to pass it to new user modal.
   */
  setChosenUserMobile = (code) => {
    this.setState({
      chosenUserMobile: code,
    });
  };

  /**
   * Handle action on click of 'Not you' link. It should close the existing
   * popup and open the 'Already a member?' popup.
   */
  handleNotYou = () => {
    // Close the header popup modal first.
    const { openHeaderModal } = this.props;
    openHeaderModal();

    // Open already a member popup.
    this.setState({
      openLinkCardModal: true,
      clickedOnNotYou: true,
    });
  };

  /**
   * Handle action on click of 'Link Aura' link. It should close the existing
   * popup and open the 'Link Aura' popup.
   */
  handleLinkAura = () => {
    // Close the header popup modal first.
    const { openHeaderModal } = this.props;
    openHeaderModal();

    // Open already a member popup.
    this.setState({
      openLinkOldCardModal: true,
    });
  };

  /**
   * Add or remove 'aura-header-modal-open' from alshayamainmenu block,
   * if any modal popup is opened or closed. It will open the modal
   * with full screen width.
   */
  toggleMenuBlockToFullWidth = () => {
    const {
      openLinkOldCardModal,
      openOtpModal,
      openNewUserModal,
      openLinkCardModal,
    } = this.state;

    if (document.querySelector('.block-alshaya-main-menu')) {
      if (openLinkOldCardModal || openOtpModal || openNewUserModal || openLinkCardModal) {
        document.querySelector('.block-alshaya-main-menu').classList.add('aura-header-modal-open');
      } else {
        document.querySelector('.block-alshaya-main-menu').classList.remove('aura-header-modal-open');
      }
    }
  };

  render() {
    const {
      openLinkOldCardModal,
      chosenCountryCode,
      openOtpModal,
      openNewUserModal,
      chosenUserMobile,
      openLinkCardModal,
      active,
      clickedOnNotYou,
    } = this.state;

    const {
      isHeaderModalOpen,
      cardNumber,
      notYouFailed,
      openHeaderModal,
      firstName,
      lastName,
      tier,
    } = this.props;

    const { baseUrl, pathPrefix } = drupalSettings.path;

    // Set the accordion toggle class.
    const activeClass = active ? 'active' : '';

    // Aura card tier class variable. By default 'no-tier'.
    let tierClass = 'no-tier';
    // Get Aura user details.
    const { id: userId } = getUserDetails();
    let profileInfo = null;
    if (userId) {
      // Prepare the user's aura profile information, if logged in.
      profileInfo = getUserProfileInfo(firstName, lastName);

      // Assign the current user's card tier level as a class.
      tierClass = tier || tierClass;
    }

    // When page is visited on mobile view, and if any modal is opened,
    // Below method will add few classes,
    // So that the modal will cover the full screen width.
    // Class will be removed when all modal popups are closed.
    this.toggleMenuBlockToFullWidth();

    return (
      <>
        {isHeaderModalOpen
          && (
            <div className={`aura-header-popup-wrapper sign-up-complete aura-level-${tierClass} ${activeClass}`}>
              <div className="aura-header-wrapper-mobile">
                <ConditionalView condition={window.innerWidth < 1024}>
                  <div className="accordion-header" onClick={() => this.handleAccordionStatus()}>
                    <AuraLogo stacked="horizontal" />
                    <span className="accordion-icon" />
                  </div>
                </ConditionalView>
                <div className={`aura-popup-header card-wrapper ${activeClass}`}>
                  <div className="heading-section">
                    {hasValue(profileInfo)
                      && (
                        <>
                          <span className="aura-user-name">{profileInfo.profileName}</span>
                          <span
                            className="not-you"
                            onClick={() => {
                              this.handleNotYou();
                              Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
                            }}
                          >
                            {getNotYouLabel(notYouFailed)}
                          </span>
                        </>
                      )}
                    <div className="close-icon" onClick={() => openHeaderModal()} />
                  </div>
                  <div className="content-section">
                    <div className="title">
                      {Drupal.t('Aura account number', {}, { context: 'aura' })}
                    </div>
                    <Cleave
                      name="aura-my-account-link-card"
                      className="aura-my-account-link-card"
                      disabled
                      value={cardNumber}
                      options={{ blocks: [4, 4, 4, 4] }}
                    />
                  </div>
                  <div className="footer-section">
                    <div className="know-more-wrapper">
                      <a href={`${baseUrl}${pathPrefix}user/loyalty-club`}>
                        {Drupal.t(
                          'Learn more',
                          {},
                          { context: 'aura' },
                        )}
                      </a>
                    </div>
                    <ConditionalView condition={!hasValue(userId)}>
                      <div className="not-you-wrapper">
                        <div className="not-you-loader-placeholder" />
                        <div className="error-placeholder" />
                        <div
                          className="not-you"
                          onClick={() => {
                            this.handleNotYou();
                            Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_SIGN_IN_NOT_YOU', label: 'initiated' });
                          }}
                        >
                          {getNotYouLabel(notYouFailed)}
                        </div>
                      </div>
                    </ConditionalView>
                    <ConditionalView condition={hasValue(userId)}>
                      <div
                        className="link-aura-link"
                        onClick={() => {
                          Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                          this.handleLinkAura();
                        }}
                      >
                        {getStringMessage('aura_link_aura')}
                      </div>
                    </ConditionalView>
                  </div>
                </div>
              </div>
            </div>
          )}
        {/** Link Aura email matching logged-in users. */}
        <Popup
          className="aura-modal-form link-old-card-modal"
          open={openLinkOldCardModal}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormUnlinkedCard
            closeLinkOldCardModal={() => this.setState({
              openLinkOldCardModal: false,
            })}
            openOTPModal={() => this.setState({
              openOtpModal: true,
              openLinkOldCardModal: false,
            })}
            openLinkCardModal={() => this.setState({
              openLinkCardModal: true,
              openLinkOldCardModal: false,
            })}
            cardNumber={cardNumber}
            firstName={firstName}
          />
        </Popup>
        {/** Join Aura Step 1 */}
        <Popup
          className="aura-modal-form otp-modal"
          open={openOtpModal}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormSignUpOTPModal
            closeOTPModal={() => this.setState({
              openOtpModal: false,
            })}
            openNewUserModal={() => this.setState({
              openNewUserModal: true,
            })}
            setChosenCountryCode={this.setChosenCountryCode}
            setChosenUserMobile={this.setChosenUserMobile}
            chosenCountryCode={chosenCountryCode}
          />
        </Popup>
        {/** Join Aura Step 2 */}
        <Popup
          className="aura-modal-form new-aura-user"
          open={openNewUserModal}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormNewAuraUserModal
            chosenCountryCode={chosenCountryCode}
            chosenUserMobile={chosenUserMobile}
            closeNewUserModal={() => this.setState({
              openNewUserModal: false,
            })}
            openLinkCardModal={() => this.setState({
              openLinkCardModal: true,
              openNewUserModal: false,
            })}
          />
        </Popup>
        {/** Already a member popup */}
        <Popup
          className="aura-modal-form link-card-otp-modal"
          open={openLinkCardModal}
          closeOnEscape={false}
          closeOnDocumentClick={false}
        >
          <AuraFormLinkCardOTPModal
            closeLinkCardOTPModal={() => this.setState({
              openLinkCardModal: false,
            })}
            openOTPModal={() => this.setState({
              openOtpModal: true,
              openLinkCardModal: false,
            })}
            setChosenCountryCode={this.setChosenCountryCode}
            chosenCountryCode={chosenCountryCode}
            clickedOnNotYou={clickedOnNotYou}
          />
        </Popup>
      </>
    );
  }
}

export default SignUpCompleteHeader;
