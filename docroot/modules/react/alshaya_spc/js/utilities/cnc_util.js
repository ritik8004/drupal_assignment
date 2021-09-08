import getStringMessage from './strings';
import {
  smoothScrollToAddressField,
} from './smoothScroll';
import collectionPointsEnabled from '../../../js/utilities/pudoAramaxCollection';

/**
 * Helper to get click and collect store map icon.
 */
export const getCncStoreMapIcon = () => drupalSettings.cnc_store_map_icon || '';

/**
 * Helper to get click and collect store list icon.
 */
export const getCncCollectionPointMapIcon = () => drupalSettings.cnc_collection_point_map_icon || '';

/**
 * Helper to get click and collect section title.
 */
export const getCncSectionTitle = () => ((collectionPointsEnabled() === true)
  ? Drupal.t('Collection Point')
  : Drupal.t('Collection Store'));

/**
 * Helper to get click and collect section description.
 */
export const getCncSectionDescription = () => ((collectionPointsEnabled() === true)
  ? Drupal.t('select your preferred collection point')
  : Drupal.t('select your preferred collection store'));

/**
 * Helper to get click and collect modal title.
 */
export const getCncModalTitle = () => ((collectionPointsEnabled() === true)
  ? 'cnc_collection_point'
  : 'cnc_collection_store');

/**
 * Helper to get click and collect modal description.
 */
export const getCncModalDescription = () => ((collectionPointsEnabled() === true)
  ? 'cnc_find_your_collection_point'
  : 'cnc_find_your_nearest_store');

/**
 * Helper to get select button text in modal.
 */
export const getCncModalButtonText = () => ((collectionPointsEnabled() === true)
  ? 'cnc_select'
  : 'cnc_select_this_store');

/**
 * Helper to check if given store is a aramex/pudo collection point.
 */
export const isCollectionPoint = (store) => (store.pudo_available !== undefined
  && store.pudo_available === true);

/**
 * Helper to get contact subtitle.
 */
export const getCnCModalContactSubtitle = () => ((collectionPointsEnabled() === true)
  ? 'cnc_selected_collection_point'
  : 'cnc_selected_store');

/**
 * Helper to get cnc map icon.
 */
export const getCncMapIcon = (store) => {
  if (collectionPointsEnabled() !== true) {
    return '';
  }

  const icon = isCollectionPoint(store) ? getCncCollectionPointMapIcon() : getCncStoreMapIcon();

  return icon;
};

/**
 * Helper to get store/collection point title.
 */
export const getPickUpPointTitle = (store) => store.collection_point || '';

/**
 * Helper to get cnc delivery time prefix.
 */
export const getCncDeliveryTimePrefix = () => ((collectionPointsEnabled() === true)
  ? 'cnc_collection_collect_in_store'
  : 'cnc_collect_in_store');

/**
 * Validate collector information.
 */
export const validateCollectorInfo = (e) => {
  let isError = false;
  const name = e.target.elements.collectorFullname.value.trim();
  let splitedName = name.split(' ');
  splitedName = splitedName.filter((s) => (
    (s.trim().length > 0
    && (s !== '\\n' && s !== '\\t' && s !== '\\r'))));
  if (name.length === 0 || splitedName.length === 1) {
    document.getElementById('collectorFullname-error').innerHTML = getStringMessage('form_error_full_name');
    document.getElementById('collectorFullname-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('collectorFullname-error').innerHTML = '';
    document.getElementById('collectorFullname-error').classList.remove('error');
  }

  const mobile = e.target.elements.collectorMobile.value.trim();
  if (mobile.length === 0
    || mobile.match(/^[0-9]+$/) === null) {
    document.getElementById('collectorMobile-error').innerHTML = getStringMessage('form_error_mobile_number');
    document.getElementById('collectorMobile-error').classList.add('error');
    isError = true;
  } else if (mobile === e.target.elements.mobile.value.trim()) {
    // Validate to ensure collectors mobile and contact mobile is not same.
    document.getElementById('collectorMobile-error').innerHTML = getStringMessage('form_error_valid_collector_mobile_number');
    document.getElementById('collectorMobile-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('collectorMobile-error').innerHTML = '';
    document.getElementById('collectorMobile-error').classList.remove('error');
  }

  const email = e.target.elements.collectorEmail.value.trim();
  if (email.length === 0) {
    document.getElementById('collectorEmail-error').innerHTML = getStringMessage('form_error_email');
    document.getElementById('collectorEmail-error').classList.add('error');
    isError = true;
  } else {
    document.getElementById('collectorEmail-error').innerHTML = '';
    document.getElementById('collectorEmail-error').classList.remove('error');
  }

  // Scroll to collectors section if error.
  if (isError) {
    smoothScrollToAddressField(
      document.querySelector('.spc-checkout-collector-information-fields > div > div.error:not(:empty)'),
      true,
    );
  }

  return isError;
};
