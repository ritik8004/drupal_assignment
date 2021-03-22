import React from 'react';
import {
  getStorageInfo,
} from '../../../utilities/storage';
import { handleFeedbackSubmit } from '../../../utilities/feedback_util';

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
    const event = new CustomEvent('handleFeedbackState', {
      bubbles: true,
      detail: {
        contentId,
      },
    });
    document.dispatchEvent(event);
    this.setState({ positiveCount: positiveCount + 1 });
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
