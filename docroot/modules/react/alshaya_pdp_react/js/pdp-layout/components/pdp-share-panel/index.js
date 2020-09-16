import React from 'react';
import parse from 'html-react-parser';
import CopyPageLink from './copy-page-link';
import PdpSectionTitle from '../utilities/pdp-section-title';
import setupAccordionHeight from '../../../utilities/sidebarCardUtils';

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
  };

  render() {
    const { sharethis } = drupalSettings;
    const { open } = this.state;
    // Add correct class.
    const expandedState = open === true ? 'show' : '';
    const sharethisContent = sharethis !== undefined ? parse(sharethis.content) : null;

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
