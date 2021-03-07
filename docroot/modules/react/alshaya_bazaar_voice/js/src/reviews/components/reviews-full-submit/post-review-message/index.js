import React from 'react';
import DisplayStar from '../../../../rating/components/stars/DisplayStar';

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
            <span className="review-recommendation-text">
              {Drupal.t('Yes, I would like to recommend this product.')}
            </span>
          </div>
        )
        : ''}
    </div>
    <div className="comment-moderation-block"><span className="comment-moderation-text">{Drupal.t('Thanks for submitting a review. Your review is being moderated and may take few days to appear.')}</span></div>
  </div>
);

export default PostReviewMessage;
