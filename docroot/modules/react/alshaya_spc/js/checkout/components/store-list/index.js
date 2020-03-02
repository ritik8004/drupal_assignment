import React from 'react';
import StoreItem from '../store-item';

const StoreList = ({ store_list, onStoreClick, onSelectStore, selected: selectedStore }) => {
  if (!store_list || store_list.length === 0) {
    return <>{Drupal.t('Sorry, No store found for your location.')}</>;
  }

  const storeItemClick = (e, index) => {
    onStoreClick(index);
    // Close already opened item.
    if (e.target.parentElement.classList.contains('expand')) {
      e.target.parentElement.classList.remove('expand');
      return;
    }
    // Add Class expand to the currently opened li.
    let storeList = document.querySelectorAll('#click-and-collect-list-view li.select-store');
    // Remove class expand from all.
    storeList.forEach(function (storeElement) {
      storeElement.classList.remove('expand');
    });
    e.target.parentElement.classList.add('expand');
  };

  return (
    <ul>
      {store_list.map((store, index) => {
        return (
          <li
            className={`select-store ${(selectedStore && store.code === selectedStore.code) ? 'expand' : ''}`}
            data-store-code={store.code}
            data-node={store.nid}
            data-index={index}
            key={store.code}
            onClick={(e) => storeItemClick(e, parseInt(index))}
          >
            <StoreItem store={store} onSelectStore={onSelectStore} />
          </li>
        );
      })}
    </ul>
  );
}

export default StoreList;
