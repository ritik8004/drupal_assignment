import React from 'react';
import ReviewInappropriate from '../review-inappropriate';
import ReviewFeedbackPositive from '../review-feedback-positive';
import ReviewFeedbackNegative from '../review-feedback-negative';
import { getFeedbackInfo } from '../../../utilities/feedback_util';

class ReviewFeedback extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      votedContentId: '',
    };
  }

  componentDidMount() {
    document.addEventListener('handleFeedbackState', this.handleFeedbackState);
  }

  handleFeedbackState = (event) => {
    event.preventDefault();
    const contentId = event.detail;
    if (contentId !== undefined) {
      this.setState({ votedContentId: contentId });
    }
  }

  render() {
    const {
      contentId, positiveCount, negativeCount, contentType,
    } = this.props;
    const { votedContentId } = this.state;
    let contentTypeDisplayValue = null;
    let btnStatus = 'active';
    const retrievedContentVote = getFeedbackInfo(contentType, contentId, 'positiveCount');
    if (votedContentId === contentId || retrievedContentVote !== null) {
      btnStatus = 'disabled';
    }
    if (contentType === 'review_comment') {
      contentTypeDisplayValue = 'comment';
    }
    if (contentType === 'review') {
      contentTypeDisplayValue = 'review';
    }
    if (contentId !== undefined && positiveCount !== undefined && negativeCount
      !== undefined) {
      return (
        <div className="review-feedback-vote">
          <span className="feedback-label">{Drupal.t('Was this @contentTypeDisplayValue helpful?', { '@contentTypeDisplayValue': contentTypeDisplayValue })}</span>
          <div className={`review-feedback-vote-${btnStatus}`}>
            <ReviewFeedbackPositive
              contentId={contentId}
              contentType={contentType}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
              btnStatus={btnStatus}
            />
            <ReviewFeedbackNegative
              contentId={contentId}
              contentType={contentType}
              positiveCount={positiveCount}
              negativeCount={negativeCount}
              btnStatus={btnStatus}
            />
          </div>
          <ReviewInappropriate
            contentId={contentId}
            contentType={contentType}
          />
        </div>
      );
    }
    return (null);
  }
}

export default ReviewFeedback;
