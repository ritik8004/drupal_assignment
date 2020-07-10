import React, { useEffect } from 'react';
import ConditionalView from '../../../../../common/components/conditional-view';
import StoreAddress from '../store-address';
import StoreTiming from '../store-timing';
import StoreItem from  '../store-map/StoreItem';

const StoreList = ({
  storeList, display, onStoreRadio, onStoreFinalize, selected: selectedStore, onStoreClose,
}) => {
  if (!storeList || storeList.length === 0) {
  return <div className="appointment-store-empty-store-list">{Drupal.t('Sorry, No store found for your location.')}</div>;
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
    addClassToStoreItem(e.target.parentElement.parentElement, 'selected');
    document.getElementsByClassName('appointment-store-actions')[0].classList.add('show');
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
      {storeList.map((store, index) => {
        console.log(store)
        console.log(selectedStore)
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
            index={parseInt(index, 10)}
            store={store}
            onStoreChoose={chooseStoreItem}
            onStoreExpand={expandStoreItem}
            onStoreFinalize={onStoreFinalize}
            onStoreClose={onStoreClose}
          />
        </li>
      )})}
    </ul>
  );
};

export default StoreList;


// export default class StoreList extends React.Component {
//   handleStoreSelect = (e) => {
//     const { handleStoreSelect } = this.props;
//     handleStoreSelect(e);
//   }

//   render() {
//     const {
//       storeList, activeItem, display, onStoreExpand,
//     } = this.props;

//     return (
//       <div className="store-list-inner-wrapper fadeInUp">
//         {storeList && Object.entries(storeList).map(([k, v]) => (
//           <div className="store-list-item">
//             <input
//               type="radio"
//               id={`store${k}`}
//               value={JSON.stringify(v)}
//               name="selectedStoreItem"
//               checked={activeItem === v.locationExternalId}
//               onChange={this.handleStoreSelect}
//             />
//             <label htmlFor={`store${k}`} className="select-store">
//               <span className="appointment-store-name">
//                 <span className="store-name-wrapper">
//                   <span className="store-name">{v.name}</span>
//                   <span className="distance">
//                     { v.distanceInMiles }
//                     { Drupal.t('Miles') }
//                   </span>
//                 </span>
//                 <ConditionalView condition={display === 'accordion'}>
//                   <span className="expand-btn" onClick={(e) => onStoreExpand(e)}>Expand</span>
//                 </ConditionalView>
//               </span>
//               <ConditionalView condition={display === 'accordion'}>
//                 <div className="store-address-content">
//                   <div className="store-address">
//                     <StoreAddress
//                       address={v.address}
//                     />
//                   </div>
//                   <div className="store-delivery-time">
//                     <StoreTiming
//                       timing={v.storeTiming}
//                     />
//                   </div>
//                 </div>
//               </ConditionalView>
//             </label>
//           </div>
//         ))}
//       </div>
//     );
//   }
// }
