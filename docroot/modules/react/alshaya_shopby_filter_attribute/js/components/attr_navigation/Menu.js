import React from 'react';
import { connectMenu } from 'react-instantsearch-dom';

const Menu = (props) => {
  const { items, element } = props;

  return (
    <>
      <div className="shop-by-shoe-size__label">{element.dataset.label}</div>
      <ul className="shop-by-shoe-size__list">
        {items.map((item) => (
          <li key={item.label} className="shop-by-shoe-size__list-item">
            <a
              href={Drupal.url('shop-men/--size_shoe_eu-42')}
            >
              {item.label}
            </a>
          </li>
        ))}
      </ul>
    </>
  );
};

export default connectMenu(Menu);
