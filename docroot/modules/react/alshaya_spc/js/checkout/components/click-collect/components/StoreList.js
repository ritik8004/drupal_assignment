import React from 'react';
import StoreItem from './StoreItem';

const StoreList = ({ store_list, display, onStoreRadio, onStoreFinalize, selected: selectedStore, onStoreClose }) => {
  if (!store_list || store_list.length === 0) {
    return <div className='spc-cnc-empty-store-list'>{Drupal.t('Sorry, No store found for your location.')}</div>;
  }

  const cooseStoreItem = (e, index) => {
    onStoreRadio(index);
    addClassToStoreItem(e.target.parentElement.parentElement, 'selected');
    document.getElementsByClassName('spc-cnc-store-actions')[0].classList.add('show');
  };

  const expandStoreItem = (e, index) => {
    cooseStoreItem(e, index);
    addClassToStoreItem(e.target.parentElement.parentElement, 'expand');
  }

  const addClassToStoreItem = (element, className) => {
    // Close already opened item.
    if (element.classList.contains(className)) {
      if (className === 'expand') {
        element.classList.remove(className);
      }
      return;
    }
    // Add Class expand to the currently opened li.
    let storeList = document.querySelectorAll('#click-and-collect-list-view li.select-store');
    // Remove class expand from all.
    storeList.forEach(function (storeElement) {
      storeElement.classList.remove(className);
    });
    element.classList.add(className);
  }

  return (
    <ul>
      {store_list.map((store, index) => {
        return (
          <li
            className={`select-store ${(selectedStore && store.code === selectedStore.code) ? 'selected' : ''}`}
            data-store-code={store.code}
            data-node={store.nid}
            data-index={index}
            key={store.code}
          >
            <StoreItem
              display={display || 'default'}
              index={parseInt(index)}
              store={store}
              onStoreChoose={cooseStoreItem}
              onStoreExpand={expandStoreItem}
              onStoreFinalize={onStoreFinalize}
              onStoreClose={onStoreClose}
            />
          </li>
        );
      })}
    </ul>
  );
}

export default StoreList;
