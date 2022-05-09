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

class SignUpCompleteHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
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
    });
  };

  render() {
    const {
      chosenCountryCode,
      openOtpModal,
      openNewUserModal,
      chosenUserMobile,
      openLinkCardModal,
      active,
    } = this.state;

    const {
      isHeaderModalOpen,
      cardNumber,
      notYouFailed,
      openHeaderModal,
    } = this.props;

    const { baseUrl, pathPrefix } = drupalSettings.path;
    const activeClass = active ? 'active' : '';

    return (
      <>
        {isHeaderModalOpen
          && (
            <div className="aura-header-popup-wrapper sign-up-complete">
              <ConditionalView condition={window.innerWidth < 1024}>
                <div className={`accordion-header ${activeClass}`} onClick={() => this.handleAccordionStatus()}>
                  <AuraLogo stacked="horizontal" />
                  <span className="accordion-icon" />
                </div>
              </ConditionalView>
              <div className={`aura-popup-header card-wrapper ${activeClass}`}>
                <div className="heading-section">
                  <a className="close-icon" onClick={() => openHeaderModal()}>X</a>
                </div>
                <div className="content-section">
                  <div className="title">
                    {Drupal.t('Aura account number')}
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
                        'Know More',
                        {},
                        { context: 'aura' },
                      )}
                    </a>
                  </div>
                  <div className="not-you-wrapper">
                    <div className="not-you-loader-placeholder" />
                    <div className="error-placeholder" />
                    <div
                      className="not-you"
                      onClick={() => this.handleNotYou()}
                    >
                      {getNotYouLabel(notYouFailed)}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          )}
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
          />
        </Popup>
      </>
    );
  }
}

export default SignUpCompleteHeader;
