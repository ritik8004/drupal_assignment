import React from 'react';
import Select from 'react-select';
import ConditionalView from '../../../common/components/conditional-view';
import { getDeliveryAreaList, getGovernatesList } from '../../../utilities/delivery_area_util';
import dispatchCustomEvent from '../../../utilities/events';
import SectionTitle from '../../../utilities/section-title';
import { getStorageInfo, removeStorageInfo, setStorageInfo } from '../../../utilities/storage';
import getStringMessage from '../../../utilities/strings';
import AvailableAreaItems from '../available-area-items';

export default class AreaListBlock extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      governateOptions: '',
      governateDefault: '',
      areaListItems: [],
      items: [],
      activeItem: null,
      governateDefaultLabel: drupalSettings.alshaya_spc.address_fields.area_parent.label,
    };
  }

  /**
   * Pre-populate city/area from storage values.
   */
  componentDidMount() {
    const { governateDefaultLabel } = this.state;
    const areaSelected = getStorageInfo('deliveryinfo-areadata');
    let defaultOptions = [];
    getGovernatesList().then(
      (response) => {
        const options = [];
        if (response !== null && Object.keys(response.items).length > 0) {
          response.items.forEach((item) => {
            options.push({
              value: item.location_id,
              label: item.label,
            });
          });
          if (areaSelected !== null) {
            defaultOptions = options.find(
              (element) => element.value === areaSelected.value.governate,
            );
            getDeliveryAreaList(areaSelected.value.governate).then(
              (result) => {
                if (result !== null && Object.keys(result.items).length > 0) {
                  this.setState({
                    areaListItems: result.items,
                    items: result.items,
                  });
                }
              },
            );
          } else {
            defaultOptions = [{
              value: 'none',
              label: Drupal.t(`Select ${governateDefaultLabel}`),
            }];
          }
          this.setState({
            governateOptions: options,
            governateDefault: defaultOptions,
          });
          dispatchCustomEvent('openDeliveryAreaPanel', {});
        }
      },
    );
  }

  /**
   * handle on change function for city select list.
   */
  handleSelect = (selectedOption) => {
    if (selectedOption.value) {
      const defaultOptions = [{
        value: selectedOption.value,
        label: selectedOption.label,
      }];
      this.setState({
        governateDefault: defaultOptions,
      });
      getDeliveryAreaList(selectedOption.value).then(
        (response) => {
          if (response !== null && Object.keys(response.items).length > 0) {
            this.setState({
              items: response.items,
              areaListItems: response.items,
            });
          }
        },
      );
    }
  };

  /**
   * Filter the list on search.
   */
  filterList = (e) => {
    const { areaListItems } = this.state;
    let updatedList = areaListItems;
    updatedList = updatedList.filter((item) => item.label.toLowerCase().search(
      e.target.value.toLowerCase(),
    ) !== -1);

    this.setState({
      items: updatedList,
    });
  };

  /**
   * Set active classes on selection of particular area.
   */
  handleLiClick = (e) => {
    if (e.currentTarget && e.currentTarget.parentElement.value
    && e.currentTarget.parentElement.attributes['data-parent-id'].nodeValue
    && e.currentTarget.firstChild.innerText) {
      this.setState({
        activeItem: {
          areaId: e.currentTarget.parentElement.value,
          areaParentId: parseInt(e.currentTarget.parentElement.attributes['data-parent-id'].nodeValue, 10),
          areaLabel: e.currentTarget.firstChild.innerText,
        },
      });
      // Remove the previous active class.
      const activeElem = document.querySelector('.spc-delivery-area ul#delivery-area-list-items li.active');
      if (activeElem) {
        activeElem.classList.remove('active');
        activeElem.classList.toggle('in-active');
      }
      // Set active class on the current element.
      const elem = document.querySelector(`.spc-delivery-area ul#delivery-area-list-items li#value${e.currentTarget.parentElement.value}`);
      if (elem.classList.contains('in-active')) {
        elem.classList.remove('in-active');
      }
      elem.classList.toggle('active');
    }
  };

  /**
   * Set new value of city/area in storage and refresh list on submit.
   */
  handleSubmit = (activeItem) => {
    if (activeItem !== null) {
      const { closeModal } = this.props;
      const { currentLanguage } = drupalSettings.path;
      removeStorageInfo('deliveryinfo-areadata');
      const currentArea = {
        label: {
          [currentLanguage]: activeItem.areaLabel,
        },
        value: {
          area: activeItem.areaId,
          governate: activeItem.areaParentId,
        },
      };
      setStorageInfo(currentArea, 'deliveryinfo-areadata');
      closeModal();
      dispatchCustomEvent('handleAreaSelect', activeItem);
      // Show delivery methods with cart items.
      dispatchCustomEvent('displayShippingMethods', currentArea);
    }
  };

  render() {
    const {
      governateOptions, governateDefault, items, activeItem, governateDefaultLabel,
    } = this.state;
    const { closeModal } = this.props;
    return (
      <div className="spc-delivery-wrapper">
        <div className="spc-delivery-area">
          <div className="title-block">
            <SectionTitle>{getStringMessage('check_area_availability')}</SectionTitle>
            <a className="close-modal" onClick={closeModal}>Close</a>
          </div>
          <div className="area-list-block-content">
            <div className="governate-label">{`${Drupal.t('Select')} ${governateDefaultLabel}`}</div>
            <div className="governate-drop-down">
              <Select
                classNamePrefix="spcSelect"
                className="spc-select"
                onChange={this.handleSelect}
                options={governateOptions}
                defaultValue={governateDefault}
                value={governateDefault}
                isSearchable
              />
            </div>
            <div className="area-label">{`${Drupal.t('Search area')}`}</div>
            <div className="spc-filter-panel-search-form-item">
              <input className="spc-filter-panel-search-field" type="text" placeholder={Drupal.t('e.g. Dubai')} onChange={this.filterList} />
            </div>
            <div className="delivery-type-wrapper">
              <span className="standard-delivery">{Drupal.t('Standard')}</span>
              <span className="sameday-delivery">{Drupal.t('Same Day')}</span>
              <span className="express-delivery">{Drupal.t('Express')}</span>
            </div>
            <div className="area-list-label">{`${Drupal.t('Select an area')}`}</div>
            <ConditionalView condition={items.length !== 0}>
              <ul id="delivery-area-list-items" className="area-list-wrapper">
                {items.map((item) => (
                  <AvailableAreaItems
                    attr={item.location_id}
                    value={item.label}
                    handleLiClick={this.handleLiClick}
                    parentId={item.parent_id}
                    isStandardDelivery={item.is_standard_delivery}
                    isSameDayDelivery={item.is_sameday_delivery}
                    isExpressDelivery={item.is_express_delivery}
                  />
                ))}
              </ul>
            </ConditionalView>
            <div className="actions">
              <div className="select-area-link submit">
                <a
                  onClick={() => this.handleSubmit(activeItem)}
                  href="#"
                  className="select-area-link"
                >
                  {Drupal.t('Select this area')}
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
