import React from 'react';

class ReviewFeedbackPositive extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      disabled: false,
      positiveCount: props.positiveCount,
      // negativeCount: props.negativeFeedbackCount,
    };
  }

  handlePositiveFeedbackCount = (reviewId, voteText) => (e) => {
    e.preventDefault();
    const { disabled, positiveCount } = this.state;
    if (disabled) {
      return;
    }
    this.setState({ disabled: true });
    const helpfulnessVoteObj = { reviewId, positiveCount };
    const event = new CustomEvent('handleApiResponse', {
      bubbles: true,
      detail: {
        reviewId,
        voteText,
      },
    });
    document.dispatchEvent(event);
    this.setState({ positiveCount: positiveCount + 1 });
    helpfulnessVoteObj.positiveCount += 1;
    localStorage.setItem(`helpfulnessVote-${reviewId}`, JSON.stringify(helpfulnessVoteObj));
  }

  render() {
    const { disabled, positiveCount } = this.state;
    const { reviewId } = this.props;
    const positiveText = 'Negative';
    const retrievedReviewVote = JSON.parse(localStorage.getItem(`helpfulnessVote-${reviewId}`));
    if (retrievedReviewVote != null && !disabled) {
      this.setState({ disabled: true });
    }
    if (reviewId !== undefined && positiveText !== undefined) {
      return (
        <span className="feedback-positive">
          <button value={positiveText} type="button" onClick={this.handlePositiveFeedbackCount(reviewId, positiveText)} disabled={disabled}>
            <span className="feedback-option-label">{Drupal.t('yes')}</span>
            <span className="feedback-count">
              (
              {positiveCount}
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
