import React from 'react';
import ReviewCommentRender from '../review-comment-render';
import ReviewFeedback from '../review-feedback';

class ReviewCommentDisplay extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const { reviewId, reviewsComment } = this.props;
    if (reviewsComment !== undefined && reviewsComment !== null) {
      const reviewCommentsDisplay = Object.values(reviewsComment).map((comment) => {
        if (reviewId !== null && reviewId === comment.ReviewId
          && comment.ModerationStatus === 'APPROVED' && comment.UserNickname !== null) {
          return ([
            <div className="comment-submission-details" key={comment.Id}>
              <div className="comment-submission-wrapper">
                <ReviewCommentRender
                  UserNickname={comment.UserNickname}
                  SubmissionTime={comment.SubmissionTime}
                  CommentText={comment.CommentText}
                  commentId={comment.Id}
                  contentLocale={comment.ContentLocale}
                />
                <div className="review-feedback">
                  <ReviewFeedback
                    negativeCount={comment.TotalNegativeFeedbackCount}
                    positiveCount={comment.TotalPositiveFeedbackCount}
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
    }
    return (null);
  }
}

export default ReviewCommentDisplay;
