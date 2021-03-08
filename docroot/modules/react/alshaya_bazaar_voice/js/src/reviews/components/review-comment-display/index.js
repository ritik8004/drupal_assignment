import React from 'react';
import { fetchAPIData } from '../../../utilities/api/apiData';
import ReviewCommentRender from '../review-comment-render';
import ReviewFeedback from '../review-feedback';

class ReviewCommentDisplay extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      reviewComments: '',
    };
  }

  /**
   * Get Average Overall ratings and total reviews count.
   */
  componentDidMount() {
    const { ReviewId: reviewId } = this.props;
    const params = `&filter=reviewid:${reviewId}`;
    const apiData = fetchAPIData('/data/reviewcomments.json', params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          if (result.data.hasErrors !== false) {
            this.setState({
              reviewComments: result.data.Results,
            });
          }
        } else {
          Drupal.logJavascriptError('comment-render', result.error);
        }
      });
    }
  }

  render() {
    const { reviewComments } = this.state;
    const { ReviewId } = this.props;
    const reviewCommentsData = Array.from(reviewComments);
    const reviewCommentsDisplay = reviewCommentsData.map((comment) => {
      if (ReviewId !== null && comment.ModerationStatus === 'APPROVED') {
        return ([
          <div className="comment-submission-details" key={comment.Id}>
            <div className="comment-submission-wrapper">
              <ReviewCommentRender
                UserNickname={comment.UserNickname}
                SubmissionTime={comment.SubmissionTime}
                CommentText={comment.CommentText}
              />
              <div className="review-feedback">
                <ReviewFeedback
                  negativeCount={comment.TotalNegativeFeedbackCount}
                  positiveCount={comment.TotalPositiveFeedbackCount}
                  isSyndicatedReview={comment.IsSyndicated}
                  contentId={comment.Id}
                  contentType="review_comment"
                />
              </div>
            </div>
          </div>,
        ]);
      }
      return '';
    }, {});
    if (reviewCommentsDisplay && reviewCommentsDisplay.length > 0) {
      return reviewCommentsDisplay;
    }

    return (null);
  }
}

export default ReviewCommentDisplay;
