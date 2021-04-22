import React from 'react';
import getStringMessage from '../../../../../../../js/utilities/strings';
import DisplayStar from '../../../../rating/components/stars';
import ConditionalView from '../../../../common/components/conditional-view';

const PostReviewMessage = ({
  postReviewData,
}) => (
  <div className="post-review-moderation" id="post-review-message">
    <DisplayStar
      starPercentage={postReviewData.Review.Rating}
    />
    <div className="review-title">{postReviewData.Review.Title}</div>
    <div className="review-text">{postReviewData.Review.ReviewText}</div>
    <ConditionalView condition={postReviewData.Review.IsRecommended !== null
      && postReviewData.Review.IsRecommended}
    >
      <div className="review-recommendation">
        <span className="review-recommendation-icon" />
        <span>{`${getStringMessage('yes')},`}</span>
        <span className="review-recommendation-text">
          {getStringMessage('recommend_this_product')}
        </span>
      </div>
    </ConditionalView>
    <div className="comment-moderation-block"><span className="comment-moderation-text">{getStringMessage('write_review_submission')}</span></div>
  </div>
);

export default PostReviewMessage;
