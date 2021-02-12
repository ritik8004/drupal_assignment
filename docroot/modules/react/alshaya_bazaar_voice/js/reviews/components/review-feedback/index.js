import React, {useState } from "react";
import ConditionalView from '../../../common/components/conditional-view';
import {postAPIData} from '../../../utilities/api/apiData';
import ReviewInappropriate from '../review-inappropriate'

const ReviewFeedback = ({
  NegativeFeedbackCount,
  PositiveFeedbackCount,
  IsSyndicatedReview,
  ReviewId,
}) => {
  const reviewVote = JSON.parse(localStorage.getItem("helpfulnessVote-"+ReviewId));
  if (reviewVote !== null) {
    if (reviewVote.positiveVoteCount !== null) {
      PositiveFeedbackCount = reviewVote.positiveVoteCount;
    }
  }
  if (reviewVote !== null) {
    if (reviewVote.negativeVoteCount !== null) {
      NegativeFeedbackCount = reviewVote.negativeVoteCount;
    }
  }

  // Set the initial count state to zero, 0
  const [positiveCount, setPositiveCount] = useState(PositiveFeedbackCount);
  const [negativeCount, setNegativeCount] = useState(NegativeFeedbackCount);
  const [isActive, setActive] = useState(false);
  const ConsoleFunction=(label)=>{
    const apiUri = '/data/submitfeedback.json';
    const params = `&FeedbackType=helpfulness&ContentType=review&ContentId=${ReviewId}&Vote=${label}`;
    const apiData = postAPIData(apiUri, params);
    setActive(!isActive);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
            var helpfulnessVoteObj = {
              "reviewId": ReviewId,
              "positiveVoteCount": positiveCount,
              "negativeVoteCount": negativeCount,
            }   
            if (label == 'Positive'){
              setPositiveCount(prevCount => prevCount + 1);
              helpfulnessVoteObj.positiveVoteCount = positiveCount+1;
              localStorage.setItem("helpfulnessVote-"+ReviewId, JSON.stringify(helpfulnessVoteObj));
            }
            else if (label == 'Negative') {
              setNegativeCount(prevCount => prevCount + 1);
              helpfulnessVoteObj.negativeVoteCount = negativeCount+1;
              localStorage.setItem("helpfulnessVote-"+ReviewId, JSON.stringify(helpfulnessVoteObj));
            }
            console.log(JSON.parse(localStorage.getItem("helpfulnessVote-"+ReviewId)));
        } else {
          // To Do
        }
      });
    }
  }

  if (PositiveFeedbackCount !== undefined && IsSyndicatedReview == false) {
    const retrievedReviewVote = JSON.parse(localStorage.getItem("helpfulnessVote-"+ReviewId));
    return (
      <div className={`review-feedback-vote ${isActive ? 'review-feedback-vote-active' : 'review-feedback-vote-given'}`} >
        <span className="feedback-label">{Drupal.t('Was this review helpful?')}</span>
            {retrievedReviewVote !== null ? (
              <div>
              <span className="feedback-positive">
                <button disabled onClick={() => ConsoleFunction('Positive')}>
                  <span className="feedback-option-label">{Drupal.t('yes')}</span>
                  <span className="feedback-count">( {positiveCount} )</span>
                </button>
              </span>
              <span className="feedback-negative">
              <button disabled onClick={() => ConsoleFunction('Negative')}>  
                <span className="feedback-option-label">{Drupal.t('no')}</span>
                <span className="feedback-count">( {negativeCount} )</span>
              </button>
            </span>
            </div>
            ) : (
              <div>
                <span className="feedback-positive">
                <button disabled = {isActive} onClick={() => ConsoleFunction('Positive')}>
                  <span className="feedback-option-label">{Drupal.t('yes')}</span>
                  <span className="feedback-count">( {positiveCount} )</span>
                </button>
              </span>
              <span className="feedback-negative">
              <button disabled = {isActive} onClick={() => ConsoleFunction('Negative')}>  
                <span className="feedback-option-label">{Drupal.t('no')}</span>
                <span className="feedback-count">( {negativeCount} )</span>
              </button>
            </span>
          </div>
            )}
            <ReviewInappropriate 
              ReviewId = {ReviewId}
              IsSyndicatedReview = {IsSyndicatedReview}
            />

      </div>
    );
  }
  return (null);
};

export default ReviewFeedback;
