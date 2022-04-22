import React from 'react';
import { getbazaarVoiceSettings, getLanguageCode } from '../../../utilities/api/request';
import { getTimeAgoDate } from '../../../../../../js/utilities/dateUtility';
import TranslateByGoogle from '../../../common/components/translate-by-google';
import ConditionalView from '../../../common/components/conditional-view';

class ReviewCommentRender extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }

  render() {
    const {
      UserNickname, SubmissionTime, CommentText, commentId, contentLocale,
    } = this.props;
    const bazaarVoiceSettings = getbazaarVoiceSettings();
    const countryCode = bazaarVoiceSettings.reviews.bazaar_voice.country_code;
    const enableTranslation = bazaarVoiceSettings.reviews.bazaar_voice.enable_google_translation;
    const charsLimit = bazaarVoiceSettings.reviews.bazaar_voice.translate_chars_limit;
    const locale = contentLocale.substring(0, 2);

    return (
      <div className="comment-submission-box">
        <div className="comment-user-details">
          <span className="comment-user-nickname">{UserNickname}</span>
          <span id={`${commentId}-comment-user-date`} className="comment-user-date">{getTimeAgoDate(SubmissionTime, countryCode, getLanguageCode())}</span>
        </div>
        <div className="comment-description">
          { CommentText && (
            <div id={`${commentId}-comment-description-text`} className="comment-description-text">
              {
                CommentText.split('\n').map((item, idx) => (
                  <span key={idx.toString()}>
                    {item}
                    <br />
                  </span>
                ))
              }
            </div>
          )}
        </div>
        { CommentText && (
          <ConditionalView condition={enableTranslation
            && CommentText.length < charsLimit
            && locale !== getLanguageCode()}
          >
            <TranslateByGoogle id={commentId} contentLocale={locale} contentType="comment" />
          </ConditionalView>
        )}
      </div>
    );
  }
}

export default ReviewCommentRender;
