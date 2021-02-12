import React, {useState } from "react";
import ConditionalView from '../../../common/components/conditional-view';
import {postAPIData} from '../../../utilities/api/apiData';

const ReviewInappropriate = ({
  ReviewId,
  IsSyndicatedReview,
}) => {
  // Set the initial count state to zero, 0
  const [reportedStatus, setReportedStatus] = useState("Report");
  const [isReported, setReported] = useState(false);

  const reportReview=()=>{
    const apiUri = '/data/submitfeedback.json';
    const params = `&FeedbackType=inappropriate&ContentType=review&ContentId=${ReviewId}`;
    const apiData = postAPIData(apiUri, params);
    setReported(!isReported);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined
          && result.data !== undefined
          && result.data.error === undefined) {
            var reportedVoteObj = {
              "reviewId": ReviewId,
              "reported": "Yes",
            }
            setReportedStatus("Reported");
            localStorage.setItem("reportedVote-"+ReviewId, JSON.stringify(reportedVoteObj));
            console.log(JSON.parse(localStorage.getItem("reportedVote-"+ReviewId)));
        } else {
          // To Do
        }
      });
    }
  }

  if (IsSyndicatedReview == false) {
    const reportedReviewVote = JSON.parse(localStorage.getItem("reportedVote-"+ReviewId));
    return (
        <div className="report-review-status">
        {reportedReviewVote !== null ? (
            <ConditionalView condition={window.innerWidth > 767}>
            <span className="feedback-report review-feedback-vote-active">
                <button disabled onClick={() => reportReview()}>  
                <span className="feedback-option-label">{Drupal.t('Reported')}</span>
                </button>
            </span>
            </ConditionalView>
        ) : (
            <ConditionalView condition={window.innerWidth > 767}>
            <span className="feedback-report review-feedback-vote-active">
                <button disabled = {isReported} onClick={() => reportReview()}>  
                <span className="feedback-option-label">{reportedStatus}</span>
                </button>
            </span>
            </ConditionalView>
        )}
        </div>
    );
  }
  return (null);
};

export default ReviewInappropriate;
