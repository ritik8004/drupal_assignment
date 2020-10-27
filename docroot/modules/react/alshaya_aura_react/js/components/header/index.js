import React from 'react';
import { getAuraConfig } from '../../utilities/helper';

class Header extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      isHeaderModalOpen: false,
    };
  }

  toggleHeaderModal = () => {
    this.setState((prevState) => (
      { isHeaderModalOpen: !prevState.isHeaderModalOpen }));
  };

  render() {
    const { headerLearnMoreLink } = getAuraConfig();

    const {
      isHeaderModalOpen,
    } = this.state;

    return (
      <>
        <div className="aura-header-link">
          <span
            className="join-aura"
            onClick={() => this.toggleHeaderModal()}
          >
            Header icon placeholder
          </span>
        </div>
        { isHeaderModalOpen
          && (
          <div className="aura-header-popup-wrapper">
            <div className="aura-popup-header">
              <div className="title title--one">
                {Drupal.t('Bespoke rewards.')}
              </div>
              <div className="title title--two">
                {Drupal.t('Bespoke lifestyles.')}
              </div>
            </div>
            <div className="aura-popup-sub-header">
              <h3>{Drupal.t('Say hello to Aura')}</h3>
            </div>
            <div className="aura-popup-body">
              <p>{Drupal.t('Good things come to those with taste. Say hello to Aura, a lifestyle program that inclulges your taste for refined brands and experiences')}</p>
            </div>
            <div className="aura-popup-footer">
              <div
                className="join-aura"
              >
                {Drupal.t('Sign up now')}
              </div>
              <a href={headerLearnMoreLink} className="learn-more">
                {Drupal.t('Learn More')}
              </a>
            </div>
          </div>
          )}
      </>
    );
  }
}

export default Header;
