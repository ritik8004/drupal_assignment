import React from 'react';
import { getDate } from '../../../../../../js/utilities/dateUtility';
import ReviewPhoto from '../../../reviews/components/review-photo';

export default class UserReviewsDescription extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const {
      reviewsIndividualSummary,
    } = this.props;
    const reviewDate = getDate(reviewsIndividualSummary.SubmissionTime);
    return (
      <div className="reviews-block">
        <div className="review-title">{reviewsIndividualSummary.Title}</div>
        <div className="review-date">{reviewDate}</div>
        <div className="review-text">{reviewsIndividualSummary.ReviewText}</div>
        <div className="review-photos">
          {
                (reviewsIndividualSummary.Photos && reviewsIndividualSummary.Photos.length > 0)
                  ? <ReviewPhoto photoCollection={reviewsIndividualSummary.Photos} />
                  : null
            }
        </div>
      </div>
    );
  }
}
