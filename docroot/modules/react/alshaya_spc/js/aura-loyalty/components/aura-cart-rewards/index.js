import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import PointsToEarnMessage from '../utilities/points-to-earn';
import ConditionalView from '../../../common/components/conditional-view';
import PointsPromoMessage from '../utilities/points-promo-message';
import PointsExpiryMessage from '../utilities/points-expiry-message';

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
        <ConditionalView condition={uid < 1}>
          <div className="block-content guest-user">
            <PointsToEarnMessage points={points} />
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
        <ConditionalView condition={uid > 0}>
          <div className="block-content registered-user-linked">
            <PointsToEarnMessage points={points} />
            <div className="actions">
              <PointsPromoMessage />
              <PointsExpiryMessage />
            </div>
          </div>
        </ConditionalView>
      </div>
    );
  }
}

export default AuraCartRewards;
