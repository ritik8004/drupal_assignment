import React from 'react';
import AreaListBlock from '../../../cart/components/area-list-block';
import ConditionalView from '../../../common/components/conditional-view';
import { getStorageInfo } from '../../../utilities/storage';
import getStringMessage from '../../../utilities/strings';

export default class PdpSelectArea extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      areaLabel: null,
    };
  }

  componentDidMount() {
    document.addEventListener('handleAreaSelect', this.handleAreaSelect);
    this.setAreaLabel();
  }

  handleAreaSelect = (event) => {
    event.preventDefault();
    this.setAreaLabel();
  }

  setAreaLabel() {
    const currentArea = getStorageInfo('deliveryinfo-areadata');
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      this.setState({
        areaLabel: currentArea.label[currentLanguage],
      });
    }
  }

  closeModal = () => {
    const {
      removePanelData,
    } = this.props;

    document.querySelector('body').classList.remove('overlay-delivery-area');
    setTimeout(() => {
      removePanelData();
    }, 400);
  };

  openModal = () => {
    // to make sure that markup is present in DOM.
    setTimeout(() => {
      document.querySelector('body').classList.add('overlay-delivery-area');
    }, 150);
    return (
      <AreaListBlock
        closeModal={() => this.closeModal()}
      />
    );
  };

  render() {
    const { areaLabel } = this.state;
    const { getPanelData } = this.props;
    return (
      <div id="pdp-area-select">
        <div className="delivery-area-label">
          <ConditionalView condition={areaLabel !== null}>
            <span>{`${Drupal.t('Selected Area')}: `}</span>
            <span onClick={() => getPanelData(this.openModal())} className="delivery-area-name">{areaLabel}</span>
          </ConditionalView>
          <ConditionalView condition={areaLabel === null}>
            <span onClick={() => getPanelData(this.openModal())}>{getStringMessage('check_area_availability')}</span>
          </ConditionalView>
        </div>
      </div>
    );
  }
}
