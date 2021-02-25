import React from 'react';

class ReviewFeedbackPositive extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      positiveCount: props.positiveCount,
      negativeCount: props.negativeCount,
    };
  }

  handlePositiveCount = (reviewId, voteText) => (e) => {
    e.preventDefault();
    const { positiveCount, negativeCount } = this.state;
    const helpfulnessVoteObj = { reviewId, positiveCount, negativeCount };
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
    const { positiveCount } = this.state;
    const { reviewId } = this.props;
    const positiveText = 'Positive';
    const retrievedReviewVote = JSON.parse(localStorage.getItem(`helpfulnessVote-${reviewId}`));
    if (reviewId !== undefined && positiveText !== undefined) {
      return (
        <span className="feedback-positive">
          <button value={positiveText} type="button" onClick={this.handlePositiveCount(reviewId, positiveText)} disabled={retrievedReviewVote !== null}>
            <span className="feedback-option-label">{Drupal.t('yes')}</span>
            <span className="feedback-count">
              (
              {retrievedReviewVote !== null ? retrievedReviewVote.positiveCount : positiveCount}
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
