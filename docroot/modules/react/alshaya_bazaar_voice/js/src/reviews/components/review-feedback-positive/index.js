import React from 'react';
import { handleFeedbackSubmit, getFeedbackInfo } from '../../../utilities/feedback_util';
import getStringMessage from '../../../../../../js/utilities/strings';
import dispatchCustomEvent from '../../../../../../js/utilities/events';
import { trackFeaturedAnalytics } from '../../../utilities/analytics';

class ReviewFeedbackPositive extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      positiveCount: props.positiveCount,
      negativeCount: props.negativeCount,
    };
  }

  handlePositiveCount = (contentId, voteText, contentType) => (e) => {
    e.preventDefault();
    const { positiveCount, negativeCount } = this.state;
    handleFeedbackSubmit(contentId, voteText, contentType, positiveCount, negativeCount);
    dispatchCustomEvent('handleFeedbackState', contentId);
    this.setState({ positiveCount: positiveCount + 1 });

    // Process positive feedback click data as user clicks on yes.
    const analyticsData = {
      type: 'Used',
      name: 'helpfulness',
      detail1: 'positive',
      detail2: contentType,
    };
    trackFeaturedAnalytics(analyticsData);
  }

  render() {
    const { positiveCount } = this.state;
    const { contentId, contentType, btnStatus } = this.props;
    const positiveText = 'Positive';
    const feedbackStorage = getFeedbackInfo(contentType, contentId, 'positiveCount');
    if (contentId !== undefined && positiveText !== undefined) {
      return (
        <span className="feedback-positive">
          <button value={positiveText} type="button" onClick={this.handlePositiveCount(contentId, positiveText, contentType)} disabled={btnStatus !== 'active'}>
            <span className="feedback-option-label">{getStringMessage('yes')}</span>
            <span className="feedback-count">
              (
              {feedbackStorage !== null ? feedbackStorage.positiveCount : positiveCount}
              )
            </span>
          </button>
        </span>
      );
    }
    return (null);
  }
}

export default ReviewFeedbackPositive;
