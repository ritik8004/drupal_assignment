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

  handleNegativeCount = (contentId, voteText, contentType) => (e) => {
    e.preventDefault();
    const { positiveCount, negativeCount } = this.state;
    const helpfulnessVoteObj = { contentId, positiveCount, negativeCount };
    const event = new CustomEvent('handleFeedbackSubmit', {
      bubbles: true,
      detail: {
        contentId,
        voteText,
        contentType,
      },
    });
    document.dispatchEvent(event);
    this.setState({ negativeCount: negativeCount + 1 });
    helpfulnessVoteObj.negativeCount += 1;
    setStorageInfo(helpfulnessVoteObj, `${contentType}-helpfulnessVote-${contentId}`);
  }

  render() {
    const { negativeCount } = this.state;
    const { contentId, contentType } = this.props;
    const negativeText = 'Negative';
    const retrievedContentVote = getStorageInfo(`${contentType}-helpfulnessVote-${contentId}`);
    if (contentId !== undefined && negativeText !== undefined) {
      return (
        <span className="feedback-negative">
          <button value={negativeText} type="button" onClick={this.handleNegativeCount(contentId, negativeText, contentType)} disabled={retrievedContentVote !== null}>
            <span className="feedback-option-label">{Drupal.t('no')}</span>
            <span className="feedback-count">
              (
              {retrievedContentVote !== null ? retrievedContentVote.negativeCount : negativeCount}
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
