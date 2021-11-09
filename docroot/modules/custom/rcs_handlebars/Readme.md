# RCS Handlebars
Provides integration with Handlebars (https://handlebarsjs.com/)

### Installation
Install the module as usual

### Using it
When you enable the module, nothing happens by default. Other modules need to
implement hook_rcs_handlebars_templates() or hook_rcs_handlebars_templates_alter()
to provide the contents of Handlebars templates, see rcs_handlebars.api.php. i.e.

```php
/**
 * Implements hook_rcs_handlebars_templates().
 */
function hook_rcs_handlebars_templates(\Drupal\Core\Entity\EntityInterface $entity) {
  // List of handlebars templates to be embedded on the page.
  return [
    // List of field templates.
    'field' => [
      // Entity name.
      'page' => [
        // Field Name and path.
        'title' => "/path/template.handlebars",
      ],
    ],
  ];
}
```

The next step is to call the renderer, passing the path to the object and data i.e.

```javascript
let data = {
  foo: 'bar'
};

let html = handlebarsRenderer.render('field.page.title', data);
```

The templates need to have the Handlebars syntax, see https://handlebarsjs.com/guide/
