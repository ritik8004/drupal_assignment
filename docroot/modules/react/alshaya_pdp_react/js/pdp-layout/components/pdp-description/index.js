import React from 'react';
import Popup from 'reactjs-popup';
import PdpSectionTitle from '../utilities/pdp-section-title';
import PdpSectionText from '../utilities/pdp-section-text';
import DescriptionContent from '../pdp-desc-popup-content';

export default class PdpDescription extends React.PureComponent {
  constructor(props) {
    super(props);
    this.state = {
      open: false,
    };
  }

  openModal = () => {
    this.setState({
      open: true,
    });
    document.querySelector('body').classList.add('desc-overlay');
  };

  closeModal = () => {
    this.setState({
      open: false,
    });
    document.querySelector('body').classList.remove('desc-overlay');
  };

  render() {
    const { open } = this.state;
    const {
      pdpShortDesc, pdpDescription, skuCode, finalPrice, pdpProductPrice, title,
    } = this.props;
    return (
      <div className="magv2-pdp-description-wrapper card">
        <PdpSectionTitle>{Drupal.t('product details')}</PdpSectionTitle>
        <PdpSectionText className="short-desc"><p>{pdpShortDesc}</p></PdpSectionText>
        <div className="magv2-desc-readmore-link" onClick={(e) => this.openModal(e)}>
          {Drupal.t('Read more')}
          <Popup
            open={open}
            onClose={this.closeModal}
            closeOnEscape
            closeOnDocumentClick={false}
          >
            <>
              <DescriptionContent
                closeModal={this.closeModal}
                title={title.label}
                pdpProductPrice={pdpProductPrice}
                finalPrice={finalPrice}
                skuCode={skuCode}
                pdpDescription={pdpDescription}
              />
            </>
          </Popup>
        </div>
      </div>
    );
  }
}
