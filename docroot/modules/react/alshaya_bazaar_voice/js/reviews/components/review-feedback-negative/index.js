import React from 'react';
import {
  setStorageInfo,
  getStorageInfo,
} from '../../../utilities/storage';

class ReviewFeedbackNegative extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      positiveCount: props.positiveCount,
      negativeCount: props.negativeCount,
    };
  }

  handleNegativeCount = (reviewId, voteText) => (e) => {
    e.preventDefault();
    const { positiveCount, negativeCount } = this.state;
    const helpfulnessVoteObj = { reviewId, positiveCount, negativeCount };
    const event = new CustomEvent('handleFeedbackSubmit', {
      bubbles: true,
      detail: {
        reviewId,
        voteText,
      },
    });
    document.dispatchEvent(event);
    this.setState({ negativeCount: negativeCount + 1 });
    helpfulnessVoteObj.negativeCount += 1;
    setStorageInfo(helpfulnessVoteObj, `helpfulnessVote-${reviewId}`);
  }

  render() {
    const { negativeCount } = this.state;
    const { reviewId } = this.props;
    const negativeText = 'Negative';
    const retrievedReviewVote = getStorageInfo(`helpfulnessVote-${reviewId}`);
    if (reviewId !== undefined && negativeText !== undefined) {
      return (
        <span className="feedback-negative">
          <button value={negativeText} type="button" onClick={this.handleNegativeCount(reviewId, negativeText)} disabled={retrievedReviewVote !== null}>
            <span className="feedback-option-label">{Drupal.t('no')}</span>
            <span className="feedback-count">
              (
              {retrievedReviewVote !== null ? retrievedReviewVote.negativeCount : negativeCount}
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
