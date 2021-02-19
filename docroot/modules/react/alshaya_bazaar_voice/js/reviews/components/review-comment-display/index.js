import React from 'react';
import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
import { fetchAPIData } from '../../../utilities/api/apiData';
import {
  removeFullScreenLoader,
  showFullScreenLoader,
} from '../../../../../js/utilities/showRemoveFullScreenLoader';

class ReviewCommentDisplay extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      ReviewComments: '',
    };
  }

  /**
   * Get Average Overall ratings and total reviews count.
   */
  componentDidMount() {
    showFullScreenLoader();
    const apiUri = '/data/reviewcomments.json';
    const { ReviewId: reviewId } = this.props;
    const params = `&filter=reviewid:${reviewId}`;
    const apiData = fetchAPIData(apiUri, params);
    if (apiData instanceof Promise) {
      apiData.then((result) => {
        if (result.error === undefined && result.data !== undefined) {
          removeFullScreenLoader();
          if (result.data.hasErrors !== false) {
            this.setState({
              ReviewComments: result.data.Results,
            });
          }
        } else {
          // Todo
        }
      });
    }
  }

  render() {
    TimeAgo.addLocale(en);
    const timeAgo = new TimeAgo('en-US');
    const { ReviewComments } = this.state;
    const { ReviewId: reviewId } = this.props;
    const data = Array.from(ReviewComments);
    const abc = data.map((comment) => {
      if (reviewId !== null && comment.ModerationStatus === 'APPROVED') {
        return ([
          <div className="comment-submission-details" key={comment.Id}>
            <div className="comment-user-details">
              <span className="comment-user-nickname">{comment.UserNickname}</span>
              <span className="comment-user-date">{timeAgo.format(new Date(comment.SubmissionTime))}</span>
            </div>
            <div className="comment-description">
              <span className="comment-description-text">{comment.CommentText}</span>
            </div>
          </div>,
        ]);
      }
      return '';
    }, {});
    return abc;
  }
}

export default ReviewCommentDisplay;
