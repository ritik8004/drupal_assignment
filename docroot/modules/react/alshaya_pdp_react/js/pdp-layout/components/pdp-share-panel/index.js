import React from 'react';
import {
  FacebookIcon,
  TwitterIcon,
  WhatsappIcon,
  TwitterShareButton,
  FacebookShareButton,
  WhatsappShareButton,
} from 'react-share';

class PdpSharePanel extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      copySuccess: '',
      shareUrl: window.location.href,
    };
  }

  copyToClipboard = () => {
    const value = this.textArea;
    value.select();
    document.execCommand('copy');
    this.setState({
      copySuccess: Drupal.t('copied'),
    });
  }

  render() {
    const { title } = this.props;
    const { copySuccess, shareUrl } = this.state;

    return (
      <div className="pdp-share-panel">
        <WhatsappShareButton
          url={shareUrl}
          title={title}
          className="twitter-button"
        >
          <WhatsappIcon size={32} round />
        </WhatsappShareButton>
        <FacebookShareButton
          url={shareUrl}
          title={title}
          className="twitter-button"
        >
          <FacebookIcon size={32} round />
        </FacebookShareButton>
        <TwitterShareButton
          url={shareUrl}
          title={title}
          className="twitter-button"
        >
          <TwitterIcon size={32} round />
        </TwitterShareButton>
        <div>
          <div>
            <textarea
              ref={(textarea) => { this.textArea = textarea; }}
              defaultValue={shareUrl}
            />
          </div>
          <div>
            <button onClick={() => this.copyToClipboard()} type="button">
              {Drupal.t('Copy page link')}
            </button>
            {copySuccess}
          </div>
        </div>
      </div>
    );
  }
}

export default PdpSharePanel;
