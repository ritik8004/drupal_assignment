import React from 'react';
import Popup from 'reactjs-popup';

export default class SharePopup extends React.Component {
  constructor(props) {
    super(props);

    this.state = {
      // To identify if share link is copied or not.
      copyLinkStatus: false,
    };
  }

  closeModal = () => {
    const { closeWishlistModal } = this.props;
    // If user simply clicks on close, we pass false as response.
    closeWishlistModal(false);
  }

  copyShareLink = () => {
    // Set the copyLinkStatus to true when link is copied.
    // @todo: change the url with the actual share link.
    navigator.clipboard.writeText('https://www.google.com/');
    this.setState({ copyLinkStatus: true });
  }

  render() {
    const { copyLinkStatus } = this.state;

    return (
      <div className="wishlist-share-popup-container">
        <Popup
          open
          className="wishlist-share"
          closeOnDocumentClick={false}
          closeOnEscape={false}
        >
          <div className="wishlist-share-popup-block">
            <a
              className="close-modal"
              onClick={() => this.closeModal()}
            >
              {Drupal.t('Close')}
            </a>
            <div className="header">{Drupal.t('Share Your List')}</div>
            <div className="content">
              {Drupal.t('Share all your faviourites with friends and family.')}
            </div>
            <div className="actions">
              <button
                type="button"
                className="email-share-link"
              >
                {Drupal.t('Email')}
              </button>
              {copyLinkStatus
                ? (
                  <span
                    className="copy-share-link link-copied"
                  >
                    {Drupal.t('Link Copied')}
                  </span>
                )
                : (
                  <button
                    type="button"
                    className="copy-share-link"
                    onClick={this.copyShareLink}
                  >
                    {Drupal.t('Copy Link')}
                  </button>
                )}
            </div>
          </div>
        </Popup>
      </div>
    );
  }
}
