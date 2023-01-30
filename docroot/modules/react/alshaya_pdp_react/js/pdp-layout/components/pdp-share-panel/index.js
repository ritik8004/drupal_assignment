import React from 'react';
import parse from 'html-react-parser';
import CopyPageLink from './copy-page-link';
import PdpSectionTitle from '../utilities/pdp-section-title';
import setupAccordionHeight from '../../../utilities/sidebarCardUtils';
import SocialShareSVG from '../../../svg-component/social-share-card-svg';

class PdpSharePanel extends React.Component {
  constructor(props) {
    super(props);
    this.expandRef = React.createRef();
    this.state = {
      open: true,
    };
  }

  componentDidMount() {
    // Accordion setup.
    setupAccordionHeight(this.expandRef);
  }

  showSharePanel = () => {
    const { open } = this.state;

    if (open) {
      this.setState({
        open: false,
      });
      this.expandRef.current.classList.add('close-card');
    } else {
      this.setState({
        open: true,
      });
      this.expandRef.current.classList.remove('close-card');
    }

    const action = open === true ? 'close' : 'open';
    // Push share this page open/close event to GTM.
    Drupal.alshayaSeoGtmPushEcommerceEvents({
      eventAction: 'share this page',
      eventLabel: action,
    });
  };

  render() {
    const { sharethis } = drupalSettings;
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    const sharethisContent = sharethis !== undefined ? parse(sharethis.contentRendered) : null;

    return (
      <div
        className="magv2-pdp-share-wrapper card fadeInUp"
        style={{ animationDelay: '1.4s' }}
        ref={this.expandRef}
      >
        <div
          className={`magv2-share-title-wrapper title ${expandedState}`}
          onClick={() => this.showSharePanel()}
        >
          <PdpSectionTitle>
            <span className="magv2-card-icon-svg">
              <SocialShareSVG />
            </span>
            {Drupal.t('Share this page')}
          </PdpSectionTitle>
          <div className="magv2-accordion" />
        </div>
        <div className="pdp-share-panel content">
          <div className="sharethis-wrapper">
            {sharethisContent}
          </div>
          <CopyPageLink />
        </div>
      </div>
    );
  }
}

export default PdpSharePanel;
