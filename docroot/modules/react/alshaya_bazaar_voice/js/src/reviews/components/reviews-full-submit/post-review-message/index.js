import React from 'react';
import DisplayStar from '../../../../rating/components/stars/DisplayStar';

const PostReviewMessage = ({
  postReviewData,
}) => (
  <div>
    <div>
      <DisplayStar
        starPercentage={postReviewData.Review.Rating}
      />
      <p>{postReviewData.Review.Title}</p>
      <p>{postReviewData.Review.ReviewText}</p>
      <p>
        {(postReviewData.Review.IsRecommended !== null)
          ? Drupal.t('Yes, I would like to recomend this product.')
          : ''}
      </p>
    </div>
    <p>{Drupal.t('Thanks for submitting a review. Your review is being moderated and may take few days to appear.')}</p>
  </div>
);

export default PostReviewMessage;
