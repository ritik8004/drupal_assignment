import React from 'react';
import EgiftCardThumbnail from '../egift-card-thumbnail';

const EgiftsCardList = (props) => {
  const { items, selected, handleEgiftSelect } = props;
  const style = {
    display: 'flex-grow',
  };

  return (
    <div className="egift-list-wrap" style={style}>
      <span className="selected-card-name">
        {selected.name}
      </span>
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
