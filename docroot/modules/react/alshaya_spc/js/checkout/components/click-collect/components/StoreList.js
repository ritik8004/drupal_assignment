import React, { useEffect } from 'react';
import StoreItem from './StoreItem';
import getStringMessage from '../../../../utilities/strings';

const StoreList = ({
  storeList, display, onStoreRadio, onStoreFinalize, selected: selectedStore, onStoreClose,
}) => {
  if (!storeList || storeList.length === 0) {
    return <div className="spc-cnc-empty-store-list">{getStringMessage('cnc_no_store_found')}</div>;
  }

  const removeClassFromStoreList = (className) => {
    // Add Class expand to the currently opened li.
    const tempStoreListNodes = document.querySelectorAll('#click-and-collect-list-view li.select-store');
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
    addClassToStoreItem(e.currentTarget.parentElement, 'selected');
    document.getElementsByClassName('spc-cnc-store-actions')[0].classList.add('show');
  };

  const expandStoreItem = (e, index) => {
    chooseStoreItem(e, index);
    addClassToStoreItem(e.target.parentElement.parentElement, 'expand');
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
          className={`select-store ${(selectedStore && store.code === selectedStore.code) ? 'selected' : ''}`}
          data-store-code={store.code}
          data-node={store.nid}
          data-index={index}
          key={store.code}
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
