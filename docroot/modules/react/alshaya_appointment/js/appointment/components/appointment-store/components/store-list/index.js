import React, { useEffect } from 'react';
import StoreItem from '../store-map/StoreItem';
import getStringMessage from '../../../../../../../js/utilities/strings';

const StoreList = ({
  storeList, display, onStoreRadio, onStoreFinalize, selected: selectedStore, onStoreClose,
}) => {
  if (!storeList || storeList.length === 0) {
    return <div className="appointment-store-empty-store-list">{getStringMessage('store_not_found')}</div>;
  }

  const removeClassFromStoreList = (className) => {
    // Add Class expand to the currently opened li.
    const tempStoreListNodes = document.querySelectorAll('#appointment-map-store-list-view li.select-store');
    const tempStoreList = [].slice.call(tempStoreListNodes);
    // Remove class expand from all.
    tempStoreList.forEach((storeElement) => {
      storeElement.classList.remove(className);
    });
  };

  const addClassToStoreItem = (element, className) => {
    // Close already opened item.
    if (element.classList.contains(className)) {
      if (className === 'expand') {
        element.classList.remove(className);
      }
      return;
    }
    // Add Class expand to the currently opened li.
    removeClassFromStoreList(className);
    element.classList.add(className);
  };

  const chooseStoreItem = (e, index) => {
    onStoreRadio(index);
    addClassToStoreItem(e.target.closest('li.select-store'), 'selected');
    document.getElementsByClassName('appointment-store-actions')[0].classList.add('show');
  };

  const expandStoreItem = (e, index) => {
    chooseStoreItem(e, index);
    addClassToStoreItem(e.target.closest('li.select-store'), 'expand');
  };

  useEffect(() => {
    if (!selectedStore) {
      removeClassFromStoreList('selected');
    }
  }, [storeList, selectedStore]);

  return (
    <ul>
      {storeList.map((store, index) => (
        <li
          className={`select-store ${(selectedStore && store.locationExternalId === selectedStore.locationExternalId) ? 'selected' : ''}`}
          data-store-code={store.locationExternalId}
          data-index={index}
          key={store.locationExternalId}
        >
          <StoreItem
            display={display || 'default'}
            index={parseInt(index, 10)}
            store={store}
            onStoreChoose={chooseStoreItem}
            onStoreExpand={expandStoreItem}
            onStoreFinalize={onStoreFinalize}
            onStoreClose={onStoreClose}
          />
        </li>
      ))}
    </ul>
  );
};

export default StoreList;
