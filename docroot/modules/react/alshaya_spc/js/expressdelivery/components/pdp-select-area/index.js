import React from 'react';
import AreaListBlock from '../../../cart/components/area-list-block';
import ConditionalView from '../../../common/components/conditional-view';
import { getAreaFieldKey, getDeliveryAreaStorage, getDeliveryAreaValue } from '../../../utilities/delivery_area_util';
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
    const areaFieldKey = getAreaFieldKey();
    if (currentArea !== null && areaFieldKey !== null) {
      const { currentLanguage } = drupalSettings.path;
      // Fetching label from api if user switches language.
      if (!(currentLanguage in currentArea.label)) {
        getDeliveryAreaValue(currentArea.value[areaFieldKey]).then(
          (result) => {
            if (result !== null && result.items.length > 0) {
              const areaObj = result.items.find(
                (element) => element.location_id === currentArea.value[areaFieldKey],
              );
              if (areaObj && Object.keys(areaObj).length !== 0) {
                currentArea.label[currentLanguage] = areaObj.label;
                Drupal.addItemInLocalStorage('deliveryinfo-areadata', currentArea);
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
    const areaPanelPlaceHolder = typeof (drupalSettings.areaBlockFormPlaceholder) !== 'undefined'
      ? drupalSettings.areaBlockFormPlaceholder
      : '';
    return (
      <AreaListBlock
        closeModal={() => this.closeModal()}
        placeHolderText={areaPanelPlaceHolder}
      />
    );
  };

  openDeliveryAreaPanel = (event) => {
    event.preventDefault();
    // to make sure that markup is present in DOM.
    document.querySelector('body').classList.add('overlay-delivery-area');
    // remove class loading when the delivery panel opens.
    document.querySelector('.delivery-loader').classList.remove('loading');
  }

  render() {
    const { areaLabel } = this.state;
    const { getPanelData, showCheckAreaAvailability } = this.props;
    return (
      <ConditionalView condition={showCheckAreaAvailability === true}>
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
      </ConditionalView>
    );
  }
}
