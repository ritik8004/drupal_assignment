import React from 'react';
import ShareIcon from './share-icon';
import SharePopup from './share-popup';
import ConditionalView from '../../../../js/utilities/components/conditional-view';

class WishlistShare extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showSharePopup: false,
    };
  }

  /**
   * To open the wishlist share popup.
   * Popup will show up while clicking on share link.
   */
  openWishlistModal = () => {
    this.setState({
      showSharePopup: true,
    });
  }

  /**
   * To close the wishlist share popup.
   */
  closeWishlistModal = () => {
    this.setState({
      showSharePopup: false,
    });
  };

  render() {
    const { showSharePopup } = this.state;

    return (
      <>
        <button type="button" onClick={this.openWishlistModal}>
          <span className="text">{Drupal.t('Share', {}, { context: 'wishlist' })}</span>
          <span className="icon"><ShareIcon /></span>
        </button>
        <ConditionalView condition={showSharePopup}>
          <SharePopup
            closeWishlistModal={this.closeWishlistModal}
          />
        </ConditionalView>
      </>
    );
  }
}

export default WishlistShare;
