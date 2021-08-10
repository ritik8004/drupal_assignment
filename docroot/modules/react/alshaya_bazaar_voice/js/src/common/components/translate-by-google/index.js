import React from 'react';
import getStringMessage from '../../../../../../js/utilities/strings';
import { renderTranslatedContent } from '../../../utilities/api/googleTranslate';

export default class TranslateByGoogle extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tranlateStatus: false,
    };
  }

  handleClick = (e, status) => {
    const { reviewId } = this.props;
    const { tranlateStatus } = this.state;
    // Render translated content on reviews section.
    renderTranslatedContent(reviewId, tranlateStatus);

    if (status === 'trans') {
      this.setState({ tranlateStatus: true });
    } else {
      this.setState({ tranlateStatus: false });
    }
  }

  render() {
    const { tranlateStatus } = this.state;

    return (
      <div className="translate-by-google-container">
        {!tranlateStatus ? (
          <div className="translate-by-google" onClick={(e) => this.handleClick(e, 'trans')}>
            {getStringMessage('translate_by_google')}
          </div>
        ) : (
          <div className="back-to-original" onClick={(e) => this.handleClick(e, 'notrans')}>
            {getStringMessage('back_to_original')}
          </div>
        )}
      </div>
    );
  }
}
