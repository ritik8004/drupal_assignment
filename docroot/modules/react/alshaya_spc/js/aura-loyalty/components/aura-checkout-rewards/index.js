import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import ConditionalView from '../../../common/components/conditional-view';
import ToolTip from '../../../utilities/tooltip';
import PointsPromoMessage from '../utilities/points-promo-message';
import PointsExpiryMessage from '../utilities/points-expiry-message';
import AuraFormLinkCard from '../aura-forms/aura-link-card-textbox';
import AuraFormRedeemPoints from '../aura-forms/aura-redeem-points';
import PendingEnrollmentMessage from '../utilities/pending-enrollment-message';

class AuraCheckoutRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      pointsToEarn: null,
      pointsInAccount: null,
    };
  }

  componentDidMount() {
    // @todo: API call here to fetch the points user will get based on his cart.
    // Alternatively, it might be just a simple sum of points for each product
    // in cart.
    this.setState({
      pointsToEarn: 1200,
    });

    // Get points for user from his card.
    // @todo: API call to get points from AURA account for registered.
    const { uid } = drupalSettings.user;
    if (uid > 0) {
      this.setState({
        pointsInAccount: 5000,
      });
    }
  }

  getPointsString = (points) => {
    const pointsString = `${points} ${Drupal.t('points')}`;

    return (
      <span className="spc-aura-highlight">{ pointsString }</span>
    );
  };

  getMembersToEarnMessage = (points) => {
    const toEarnMessageP1 = `${Drupal.t('Members will earn')} `;
    const pointsHighlight = this.getPointsString(points);
    const toEarnMessageP2 = ` ${Drupal.t('with this purchase')}`;

    return (
      <span className="spc-checkout-aura-points-to-earn">
        { toEarnMessageP1 }
        { pointsHighlight }
        { toEarnMessageP2 }
      </span>
    );
  };

  render() {
    const {
      animationDelay: animationDelayValue,
    } = this.props;

    const {
      pointsToEarn,
      pointsInAccount,
    } = this.state;

    const tooltip = Drupal.t('Your points will be credited to your account but will be on-hold status until the return period of 14 days. After that you will be able to redeem  the points.');

    const { uid } = drupalSettings.user;
    let sectionTitle = Drupal.t('Aura Rewards (Optional)');
    if (uid > 0) {
      sectionTitle = Drupal.t('Aura Rewards');
    }

    return (
      <div className="spc-aura-checkout-rewards-block fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{ sectionTitle }</SectionTitle>
        {/* Guest */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid < 1}>
          <div className="block-content guest-user">
            <div className="title">
              <div className="subtitle-1">{ Drupal.t('Earn and redeem as you shop ') }</div>
              <div className="subtitle-2">{ this.getMembersToEarnMessage(pointsToEarn) }</div>
            </div>
            <div className="spc-aura-link-card-form">
              <div className="label">{ Drupal.t('Already an Aura member?') }</div>
              <div className="item-wrapper">
                <AuraFormLinkCard />
                <div className="sub-text">
                  <span>{ Drupal.t('Not a member yet?') }</span>
                  <a href="#">{Drupal.t('Sign up now')}</a>
                </div>
              </div>
            </div>
          </div>
        </ConditionalView>
        {/* Registered User - Linked Card */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid > 1}>
          <div className="block-content registered-user-linked">
            <div className="title">
              <div className="subtitle-1">
                { Drupal.t('You Have') }
                :
                { this.getPointsString(pointsInAccount) }
              </div>
              <div className="subtitle-2">
                { Drupal.t('You will earn') }
                :
                { this.getPointsString(pointsToEarn) }
                { Drupal.t('with this purchase') }
                <ToolTip enable question>{ tooltip }</ToolTip>
              </div>
            </div>
          </div>
          <AuraFormRedeemPoints />
          <div className="spc-aura-checkout-messages">
            <PointsPromoMessage />
            <PointsExpiryMessage points="700" date={Drupal.t('30th June')} />
          </div>
        </ConditionalView>
        {/* Registered User - Linked Card - Pending Enrollment */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid > 1}>
          <div className="block-content registered-user-linked-pending-enrollment">
            <div className="title">
              <div className="subtitle-1">
                { Drupal.t('You Have') }
                :
                { this.getPointsString(pointsInAccount) }
              </div>
              <div className="subtitle-2">
                { Drupal.t('You will earn') }
                :
                { this.getPointsString(pointsToEarn) }
                { Drupal.t('with this purchase') }
                <ToolTip enable question>{ tooltip }</ToolTip>
              </div>
            </div>
          </div>
          <PendingEnrollmentMessage />
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCheckoutRewards;
