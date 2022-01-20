import React from 'react';
import EgiftCardThumbnail from '../egift-card-thumbnail';

const EgiftsCardList = (props) => {
  const { items, selected, handleEgiftSelect } = props;

  return (
    <div className="egift-list-wrapper">
      <div className="selected-card-name subtitle-text">
        {selected.name}
      </div>
      <ul>
        {
          items.map((item) => (
            <EgiftCardThumbnail
              key={item.id}
              item={item}
              selected={selected}
              handleEgiftSelect={handleEgiftSelect}
            />
          ))
        }
      </ul>
    </div>
  );
};

export default EgiftsCardList;
