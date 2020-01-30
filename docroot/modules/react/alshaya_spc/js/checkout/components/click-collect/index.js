import React from 'react';
import SectionTitle from '../../../utilities/section-title';

export default class ClickCollect extends React.Component {

  constructor(props) {
    super(props);
    this.searchplaceInput = React.createRef();
    this.state = {
      'store_list': null,
    };
  }

  componentDidMount() {

  }

  /**
   * When user click on deliver to current location.
   */
  deliverToCurrentLocation = () => {

  }

  render() {
    return(
      <div className="spc-address-form">
        { window.innerWidth > 768 &&
          <div className='spc-address-form-map'>
          </div>
        }
        <div className='spc-address-form-sidebar'>
          <SectionTitle>{Drupal.t('Collection Store')}</SectionTitle>
          <div className='spc-address-form-wrapper'>
            { window.innerWidth < 768 &&
              <div className='spc-address-form-map'>
              </div>
            }
            <div className='spc-address-form-content'>
              <div>{Drupal.t('Find your nearest store')}</div>
              <form className='spc-address-add' onSubmit={this.handleSubmit}>
                <div>
                  <input
                    ref={this.searchplaceInput}
                    className="form-search"
                    type="search"
                    id="edit-store-location"
                    name="store_location"
                    size="60"
                    maxLength="128"
                    placeholder={Drupal.t('enter a location')}
                    autoComplete="off"
                  />
                  <button className="cc-near-me" id="edit-near-me" onClick={this.getCurrentPosition}>{Drupal.t('Near me')}</button>
                </div>
                <div id="click-and-collect-list-view"></div>
              </form>
            </div>
          </div>
        </div>
      </div>
    );
  }

}
