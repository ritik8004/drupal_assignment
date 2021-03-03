import React from 'react';
import {
  setStorageInfo,
  getStorageInfo,
} from '../../../utilities/storage';

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
    this.setState({ positiveCount: positiveCount + 1 });
    helpfulnessVoteObj.positiveCount += 1;
    setStorageInfo(helpfulnessVoteObj, `${contentType}-helpfulnessVote-${contentId}`);
  }

  render() {
    const { positiveCount } = this.state;
    const { contentId, contentType } = this.props;
    const positiveText = 'Positive';

    const retrievedContentVote = getStorageInfo(`${contentType}-helpfulnessVote-${contentId}`);
    if (contentId !== undefined && positiveText !== undefined) {
      return (
        <span className="feedback-positive">
          <button value={positiveText} type="button" onClick={this.handlePositiveCount(contentId, positiveText, contentType)} disabled={retrievedContentVote !== null}>
            <span className="feedback-option-label">{Drupal.t('yes')}</span>
            <span className="feedback-count">
              (
              {retrievedContentVote !== null ? retrievedContentVote.positiveCount : positiveCount}
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
