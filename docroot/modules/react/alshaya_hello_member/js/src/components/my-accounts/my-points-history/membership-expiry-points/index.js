import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import setupAccordionHeight from '../../../../utilities';

export default class PointsInfoSummary extends React.PureComponent {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: true,
    };
  }

  componentDidMount() {
    // Accordion setup.
    setupAccordionHeight(this.expandRef);
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
    const { pointsSummaryInfo } = this.props;

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
              { '@points': pointsSummaryInfo.points_earned.total_points })}
          </div>
        </div>
        <div className="content">
          <div className="points-earned-block">
            <div className="earned-items">
              <div className="item">{getStringMessage('purchase')}</div>
              <div className="points">
                {getStringMessage('earned_points',
                  { '@points': pointsSummaryInfo.points_earned.purchase })}
              </div>
            </div>
            <div className="earned-items">
              <div className="item">{getStringMessage('submit_review')}</div>
              <div className="points">
                {getStringMessage('earned_points',
                  { '@points': pointsSummaryInfo.points_earned.rating_review })}
              </div>
            </div>
            <div className="earned-items">
              <div className="item">{getStringMessage('profile_complete')}</div>
              <div className="points">
                {getStringMessage('earned_points',
                  { '@points': pointsSummaryInfo.points_earned.profile_completion })}
              </div>
            </div>
          </div>
          <div className="earned-points-info">
            <div className="info-items">
              <p className="info-item-title">{getStringMessage('purchase')}</p>
              <p className="info-item-subtitle">
                {getStringMessage('purchanse_message',
                  {
                    '@currency_value': pointsSummaryInfo.points_info.currency_value,
                    '@currency_code': pointsSummaryInfo.points_info.currency_code,
                    '@points_value': pointsSummaryInfo.points_info.points_value,
                  })}
              </p>
            </div>
            <div className="info-items">
              <p className="info-item-title">{getStringMessage('submit_review')}</p>
              <p className="info-item-subtitle">
                {getStringMessage('write_review_message',
                  { '@review_points': pointsSummaryInfo.points_info.rating_review })}
              </p>
            </div>
            <div className="info-items">
              <p className="info-item-title">{getStringMessage('profile_complete')}</p>
              <p className="info-item-subtitle">
                {getStringMessage('profile_complete_message',
                  { '@profile_completion_value': pointsSummaryInfo.points_info.profile_completion })}
              </p>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
