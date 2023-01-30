import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import { setupAccordionHeight } from '../../../../utilities';
import EarnedPointsInfo from '../earned-points-info';
import EarnedPointsItem from '../earned-points-item';

export default class PointsInfoSummary extends React.PureComponent {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: false,
    };
  }

  componentDidMount() {
    // Accordion setup.
    setupAccordionHeight(this.expandRef);
    this.expandRef.current.classList.add('close-expiry-accordion');
  }

  showExpiryContent = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-expiry-accordion');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-expiry-accordion');
    }
  };

  render() {
    const { open } = this.state;
    const { pointsEarned, pointsSummary } = this.props;

    // Add correct class.
    const expandedState = open === true ? 'show' : '';

    return (
      <div
        className="expiry-accordion disabled fadeInUp"
        style={{ animationDelay: '1.2s' }}
        ref={this.expandRef}
      >
        <div
          className={`title ${expandedState}`}
          onClick={() => this.showExpiryContent()}
        >
          <div className="">{getStringMessage('points_label')}</div>
          <div className="points-accordion">
            {getStringMessage('earned_points',
              { '@points': pointsEarned.total })}
          </div>
        </div>
        <div className="content">
          <div className="points-earned-block">
            <EarnedPointsItem
              itemTitle={getStringMessage('purchase')}
              itemPoints={getStringMessage('earned_points',
                { '@points': pointsEarned.purchase })}
            />
            <EarnedPointsItem
              itemTitle={getStringMessage('submit_review')}
              itemPoints={getStringMessage('earned_points',
                { '@points': pointsEarned.rating_review })}
            />
            <EarnedPointsItem
              itemTitle={getStringMessage('profile_complete')}
              itemPoints={getStringMessage('earned_points',
                { '@points': pointsEarned.profile_complete })}
            />
          </div>
          <div className="earned-points-info">
            <EarnedPointsInfo
              infoTitle={getStringMessage('purchase')}
              infoSubtitle={getStringMessage('purchanse_message',
                {
                  '@currency_value': pointsSummary.conversion.currency_value,
                  '@currency_code': pointsSummary.conversion.currency_code,
                  '@points_value': pointsSummary.conversion.points_value,
                })}
            />
            <EarnedPointsInfo
              infoTitle={getStringMessage('submit_review')}
              infoSubtitle={getStringMessage('write_review_message',
                { '@review_points': pointsSummary.rating_review })}
            />
            <EarnedPointsInfo
              infoTitle={getStringMessage('profile_complete')}
              infoSubtitle={getStringMessage('profile_complete_message',
                { '@profile_completion_value': pointsSummary.profile_complete })}
            />
          </div>
        </div>
      </div>
    );
  }
}
