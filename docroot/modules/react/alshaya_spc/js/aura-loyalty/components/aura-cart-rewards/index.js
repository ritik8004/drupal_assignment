import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import PointsToEarnMessage from '../utilities/points-to-earn';
import ConditionalView from '../../../common/components/conditional-view';
import PointsPromoMessage from '../utilities/points-promo-message';
import PointsExpiryMessage from '../utilities/points-expiry-message';
import PendingEnrollmentMessage from '../utilities/pending-enrollment-message';
import AuraFormUnlinkedCard from '../aura-forms/aura-unlinked-card';

class AuraCartRewards extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      points: null,
    };
  }

  componentDidMount() {
    // @todo: API call here to fetch the points user will get based on his cart.
    // Alternatively, it might be just a simple sum of points for each product
    // in cart.
    this.setState({
      points: 5432,
    });
  }

  render() {
    const {
      animationDelay: animationDelayValue,
    } = this.props;

    const {
      points,
    } = this.state;

    const { uid } = drupalSettings.user;
    let sectionTitle = Drupal.t('Aura Rewards (Optional)');
    if (uid > 0) {
      sectionTitle = Drupal.t('Aura Rewards');
    }

    return (
      <div className="spc-aura-cart-rewards-block fadeInUp" style={{ animationDelay: animationDelayValue }}>
        <SectionTitle>{sectionTitle}</SectionTitle>
        {/* Guest */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid < -1}>
          <div className="block-content guest-user">
            <PointsToEarnMessage points={points} type="guest-no-card" />
            <div className="actions">
              <div className="spc-join-aura-link-wrapper submit">
                <a
                  href="#"
                  className="spc-join-aura-link"
                >
                  {Drupal.t('Sign up now')}
                </a>
              </div>
            </div>
          </div>
        </ConditionalView>
        {/* Registered with Linked Loyalty Card */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid > 10}>
          <div className="block-content registered-user-linked">
            <PointsToEarnMessage points={points} type="register-linked" />
            <div className="actions">
              <PointsPromoMessage />
              <PointsExpiryMessage points="700" date={Drupal.t('30th June')} />
            </div>
          </div>
        </ConditionalView>
        {/* Registered with Linked Loyalty Card - Pending Enrollment */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid > 110}>
          <div className="block-content registered-user-linked-pending-enrollment">
            <PointsToEarnMessage points={points} type="register-linked-pending" />
            <div className="actions">
              <PendingEnrollmentMessage />
            </div>
          </div>
        </ConditionalView>
        {/* Registered with Unlinked Loyalty Card */}
        {/* @todo: Update condition. */}
        <ConditionalView condition={uid === 0}>
          <div className="block-content registered-user-unlinked-card">
            <PointsToEarnMessage points={points} type="register-unlinked" />
            <div className="actions">
              <AuraFormUnlinkedCard />
            </div>
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCartRewards;
