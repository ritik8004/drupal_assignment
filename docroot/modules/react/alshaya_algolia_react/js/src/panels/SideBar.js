import React from 'react'

export default function SideBar(props) {
  return (
    <aside class="c-sidebar-first">
      <div class="c-sidebar-first__region">
        <div class="region region__sidebar-first clearfix">
          <div className="c-facet__blocks block-alshaya-category-lhn-block">
            {props.children}
          </div>
        </div>
      </div>
    </aside>
  );
};
