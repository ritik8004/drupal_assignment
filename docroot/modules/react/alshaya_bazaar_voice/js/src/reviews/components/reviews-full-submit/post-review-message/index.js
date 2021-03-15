import React from 'react';
import DisplayStar from '../../../../rating/components/stars/DisplayStar';
import getStringMessage from '../../../../../../../js/utilities/strings';

const PostReviewMessage = ({
  postReviewData,
}) => (
  <div className="post-review-moderation" id="post-review-message">
    <div>
      <DisplayStar
        starPercentage={postReviewData.Review.Rating}
      />
      <div className="review-title">{postReviewData.Review.Title}</div>
      <div className="review-text">{postReviewData.Review.ReviewText}</div>
      {(postReviewData.Review.IsRecommended !== null)
        ? (
          <div className="review-recommendation">
            <span className="review-recommendation-icon" />
            <span>{`${Drupal.t('yes')},`}</span>
            <span className="review-recommendation-text">
              {getStringMessage('recommend_this_product')}
            </span>
          </div>
        )
        : ''}
    </div>
    <div className="comment-moderation-block"><span className="comment-moderation-text">{getStringMessage('comment_moderation_text')}</span></div>
  </div>
);

export default PostReviewMessage;
