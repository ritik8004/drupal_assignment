# Alshaya V3 Setup

## RCS Shopby footer menu setup for new V3 Brands Themes.
* Create a new twig template `templates/layout/page--rcs-pdp.html.twig` in your brand theme and copy below code into it.
```
{% extends '@alshaya_rcs_product/page--rcs-pdp.html.twig' %}
{% block primary_footer %}
  {% if page.footer_primary %}
      {% include directory ~ '/templates/partial/footer-primary.html.twig' %}
  {% endif %}
{% endblock %}`
```
* Render `alshayarcsshopby` block in `footer-primary.html.twig` at the appropriate position in the template.
* In case its getting rendered at other positions in primary footer it can be remove like `{{ page.footer_primary|without('alshayarcsshopby') }}`
