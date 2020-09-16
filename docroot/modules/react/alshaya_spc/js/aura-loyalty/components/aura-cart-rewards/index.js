import React from 'react';
import SectionTitle from '../../../utilities/section-title';
import PointsToEarnMessage from '../utilities/points-to-earn';
import ConditionalView from '../../../common/components/conditional-view';

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
        <div className="block-content">
          <PointsToEarnMessage points={points} />
          <ConditionalView condition={uid < 1}>
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
          </ConditionalView>
        </div>
      </div>
    );
  }
}

export default AuraCartRewards;
