import React from 'react';
import LoyaltyClubBlock from './loyalty-club-block';
import LoyaltyClubTabs from './loyalty-club-tabs';
import { getAllAuraStatus } from '../../utilities/helper';
import dispatchCustomEvent from '../../../../js/utilities/events';
import { getAuraDetailsDefaultState, getLoyaltyPageContent, isMyAuraContext } from '../../utilities/aura_utils';
import { isUserAuthenticated } from '../../../../js/utilities/helper';
import { hasValue } from '../../../../js/utilities/conditionsUtility';
import LoyaltyPageContent from './loyalty-page-content';

class LoyaltyClub extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      wait: true,
      ...getAuraDetailsDefaultState(),
      notYouFailed: false,
      linkCardFailed: false,
      htmlContent: null,
    };
  }

  componentDidMount() {
    const {
      loyaltyStatus,
    } = this.state;

    document.addEventListener('customerDetailsFetched', this.setCustomerDetails, false);
    document.addEventListener('loyaltyStatusUpdated', this.setCustomerDetails, false);

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      this.setState({
        wait: false,
      });
    }

    // Set wait false for guest user.
    if (!isUserAuthenticated() && loyaltyStatus === 0) {
      this.setState({
        wait: false,
      });
    }

    // Get the static html content from the api.
    const auraInfo = getLoyaltyPageContent();
    if (auraInfo instanceof Promise) {
      auraInfo.then((response) => {
        // Update state only if html value is available.
        if (hasValue(response) && hasValue(response.html)) {
          this.setState({
            htmlContent: response.html,
          });
        }
      });
    }
  }

  setCustomerDetails = (data) => {
    const { stateValues } = data.detail;
    this.setState({
      ...stateValues,
    });
  };

  updateLoyaltyStatus = (loyaltyStatus) => {
    let stateValues = {
      loyaltyStatus,
    };

    if (loyaltyStatus === getAllAuraStatus().APC_NOT_LINKED_NOT_U) {
      stateValues = {
        ...getAuraDetailsDefaultState(),
        loyaltyStatus,
        signUpComplete: false,
      };
    }

    this.setState(stateValues);

    dispatchCustomEvent('loyaltyStatusUpdated', { stateValues });
  };

  render() {
    const {
      wait,
      loyaltyStatus,
      tier,
      points,
      cardNumber,
      expiringPoints,
      expiryDate,
      pointsOnHold,
      upgradeMsg,
      firstName,
      lastName,
      notYouFailed,
      linkCardFailed,
      htmlContent,
    } = this.state;

    return (
      <>
        <LoyaltyClubBlock
          wait={wait}
          loyaltyStatus={loyaltyStatus}
          tier={tier}
          points={points}
          cardNumber={cardNumber}
          expiringPoints={expiringPoints}
          expiryDate={expiryDate}
          pointsOnHold={pointsOnHold}
          upgradeMsg={upgradeMsg}
          firstName={firstName}
          lastName={lastName}
          notYouFailed={notYouFailed}
          linkCardFailed={linkCardFailed}
          updateLoyaltyStatus={this.updateLoyaltyStatus}
        />

        {isMyAuraContext() && drupalSettings.user.uid === 0 && (
          <LoyaltyPageContent
            htmlContent={htmlContent}
          />
        )}
        <LoyaltyClubTabs loyaltyStatus={loyaltyStatus} />
      </>
    );
  }
}

export default LoyaltyClub;
