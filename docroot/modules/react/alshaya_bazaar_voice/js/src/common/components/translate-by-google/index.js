import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';
import { renderTranslatedReview, renderTranslatedComment } from '../../../utilities/api/googleTranslate';

export default class TranslateByGoogle extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tranlateStatus: false,
    };
  }

  handleClick = (e, status) => {
    e.preventDefault();
    const { id, contentLocale, contentType } = this.props;
    const { tranlateStatus } = this.state;
    // Render translated reviews/comments on pdp section.
    if (contentType === 'comment') {
      renderTranslatedComment(id, tranlateStatus, contentLocale, contentType);
    } else {
      renderTranslatedReview(id, tranlateStatus, contentLocale, contentType);
    }

    if (status === 'trans') {
      this.setState({ tranlateStatus: true });
    } else {
      this.setState({ tranlateStatus: false });
    }
  }

  render() {
    const { tranlateStatus } = this.state;
    const { contentType } = this.props;
    const label = (contentType === 'comment') ? getStringMessage('back_to_original_comment') : getStringMessage('back_to_original_review');

    return (
      <div className="translate-by-google-container">
        {!tranlateStatus ? (
          <a className="translate-by-google" onClick={(e) => this.handleClick(e, 'trans')}>
            {getStringMessage('translate_with_google')}
          </a>
        ) : (
          <a className="back-to-original" onClick={(e) => this.handleClick(e, 'notrans')}>
            {label}
          </a>
        )}
      </div>
    );
  }
}
