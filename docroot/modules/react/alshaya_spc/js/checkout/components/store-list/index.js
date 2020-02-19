import React from 'react';
import StoreItem from '../store-item';

const StoreList = ({store_list, onStoreClick, onSelectStore}) => {
  if (!store_list) {
    return (null);
  }

  return (
    <ul>
      {store_list.map(function(store, index) {
        return (
          <li
            className="select-store"
            data-store-code={store.code}
            data-node={ store.nid }
            data-index={ index }
            key={store.code}
            onClick={() => onStoreClick(parseInt(index))}
          >
            <StoreItem store={store} onSelectStore={onSelectStore}/>
          </li>
        );
      })}
    </ul>
  );
}

export default StoreList;