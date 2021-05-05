import React from 'react';
import {
  getStorageInfo,
} from '../../../utilities/storage';
import { handleFeedbackSubmit } from '../../../utilities/feedback_util';
import getStringMessage from '../../../../../../js/utilities/strings';

class ReviewFeedbackNegative extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      positiveCount: props.positiveCount,
      negativeCount: props.negativeCount,
    };
  }

  handleNegativeCount = (contentId, voteText, contentType) => (e) => {
    e.preventDefault();
    const { positiveCount, negativeCount } = this.state;
    handleFeedbackSubmit(contentId, voteText, contentType, positiveCount, negativeCount);
    const event = new CustomEvent('handleFeedbackState', {
      bubbles: true,
      detail: {
        contentId,
      },
    });
    document.dispatchEvent(event);
    this.setState({ negativeCount: negativeCount + 1 });
  }

  render() {
    const { negativeCount } = this.state;
    const { contentId, contentType, btnStatus } = this.props;
    const negativeText = 'Negative';
    const retrievedContentVote = getStorageInfo(contentType);
    let storageNegativeCount = null;
    if (retrievedContentVote !== null) {
      retrievedContentVote.find((review) => {
        if (review.id === contentId && review.negativeCount >= 0) {
          storageNegativeCount = review.negativeCount;
        }
        return storageNegativeCount;
      });
    }
    if (contentId !== undefined && negativeText !== undefined) {
      return (
        <span className="feedback-negative">
          <button value={negativeText} type="button" onClick={this.handleNegativeCount(contentId, negativeText, contentType)} disabled={btnStatus !== 'active'}>
            <span className="feedback-option-label">{getStringMessage('no')}</span>
            <span className="feedback-count">
              (
              {storageNegativeCount !== null ? storageNegativeCount : negativeCount}
              )
            </span>
          </button>
        </span>
      );
    }
    return (null);
  }
}

export default ReviewFeedbackNegative;
