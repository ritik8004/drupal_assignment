import React from 'react';
import AreaListBlock from '../../../cart/components/area-list-block';
import ConditionalView from '../../../common/components/conditional-view';
import { getDeliveryAreaStorage, getDeliveryAreaValue } from '../../../utilities/delivery_area_util';
import { setStorageInfo } from '../../../utilities/storage';
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
    const currentArea = getDeliveryAreaStorage();
    if (currentArea !== null) {
      const { currentLanguage } = drupalSettings.path;
      // Fetching label from api if user switches language.
      if (!(currentLanguage in currentArea.label)) {
        getDeliveryAreaValue(currentArea.value.area).then(
          (result) => {
            if (result !== null && result.items.length > 0) {
              const areaObj = result.items.find(
                (element) => element.location_id === currentArea.value.area,
              );
              if (areaObj && Object.keys(areaObj).length !== 0) {
                currentArea.label[currentLanguage] = areaObj.label;
                setStorageInfo(currentArea, 'deliveryinfo-areadata');
                this.setState({
                  areaLabel: areaObj.label,
                });
              }
            }
          },
        );
      } else {
        this.setState({
          areaLabel: currentArea.label[currentLanguage],
        });
      }
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
    document.addEventListener('openDeliveryAreaPanel', this.openDeliveryAreaPanel);
    return (
      <AreaListBlock
        closeModal={() => this.closeModal()}
      />
    );
  };

  openDeliveryAreaPanel = (event) => {
    event.preventDefault();
    // to make sure that markup is present in DOM.
    document.querySelector('body').classList.add('overlay-delivery-area');
  }

  render() {
    const { areaLabel } = this.state;
    const { getPanelData } = this.props;
    return (
      <div id="pdp-area-select">
        <div className="delivery-area-label">
          <ConditionalView condition={areaLabel !== null}>
            <span>{`${Drupal.t('Selected Area')}: `}</span>
            <span onClick={() => getPanelData(this.openModal())} className="delivery-area-name delivery-loader">{areaLabel}</span>
          </ConditionalView>
          <ConditionalView condition={areaLabel === null}>
            <span className="availability-link delivery-loader" onClick={() => getPanelData(this.openModal())}>{getStringMessage('check_area_availability')}</span>
          </ConditionalView>
        </div>
      </div>
    );
  }
}
