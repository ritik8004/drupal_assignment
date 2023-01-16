import React from 'react';
import parse from 'html-react-parser';
import Popup from 'reactjs-popup';
import { renderToString } from 'react-dom/server';
import Loading from '../../../../../utilities/loading';
import AuraHorizontalIcon from '../../../../../../../alshaya_aura_react/js/svg-component/aura-horizontal-icon';
import getStringMessage from '../../../../../../../js/utilities/strings';
import ToolTip from '../../../../../utilities/tooltip';
import AuraFormUnlinkedCard from '../../../aura-forms/aura-unlinked-card';
import AuraFormSignUpOTPModal from '../../../aura-forms/aura-otp-modal-form';
import AuraFormNewAuraUserModal from '../../../aura-forms/aura-new-aura-user-form';
import AuraFormLinkCardOTPModal from '../../../aura-forms/aura-link-card-otp-modal-form';
import { isUserAuthenticated } from '../../../../../../../js/utilities/helper';
import { getTooltipPointsOnHoldMsg } from '../../../../../../../alshaya_aura_react/js/utilities/aura_utils';

class AuraNotLinkedData extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      openLinkOldCardModal: false,
      openOtpModal: false,
      chosenCountryCode: null,
      chosenUserMobile: null,
      openNewUserModal: false,
      openLinkCardModal: false,
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

  render() {
    const {
      pointsToEarn,
      cardNumber,
      firstName,
      wait,
    } = this.props;
    const {
      openLinkOldCardModal,
      chosenCountryCode,
      openOtpModal,
      openNewUserModal,
      chosenUserMobile,
      openLinkCardModal,
    } = this.state;

    // If an already registered user signed in with it's card for which MDC has
    // associated account, we receive loyalty status APC_NOT_LINKED_DATA. But
    // for as a guest user can't perform link aura function so we will show
    // points to earn message only in this scenario.
    if (!isUserAuthenticated()) {
      return (
        <>
          <div className="block-content registered-user-linked-pending-enrollment">
            <div className="spc-aura-cart-icon">
              <AuraHorizontalIcon />
            </div>
            <div className="spc-aura-cart-content">
              <span className="spc-aura-points-to-earn">
                {parse(getStringMessage('cart_earn_with_this_purchase', {
                  '!pts': wait ? renderToString(<Loading />) : pointsToEarn,
                }))}
                <ToolTip enable question>{getTooltipPointsOnHoldMsg()}</ToolTip>
              </span>
            </div>
          </div>
        </>
      );
    }

    return (
      <>
        <div className="block-content registered-user-unlinked-card">
          <div className="spc-aura-cart-icon">
            <AuraHorizontalIcon />
          </div>
          <div className="spc-aura-cart-content">
            <div className="spc-aura-points-to-earn">
              <span className="spc-link-aura-link-wrapper submit">
                <a
                  className="spc-link-aura-link"
                  onClick={() => {
                    this.setState({
                      openLinkOldCardModal: true,
                    });
                    Drupal.alshayaSeoGtmPushAuraEventData({ action: 'AURA_EVENT_ACTION_LINK_YOUR_CARD', label: 'initiated' });
                  }}
                >
                  {getStringMessage('aura_link_aura')}
                </a>
              </span>
              {parse(getStringMessage(
                'cart_to_earn_with_points',
                { '!pts': wait ? renderToString(<Loading />) : pointsToEarn },
              ))}
              <ToolTip enable question>{getStringMessage('checkout_earn_and_redeem_tooltip')}</ToolTip>
            </div>
          </div>
        </div>
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

export default AuraNotLinkedData;
