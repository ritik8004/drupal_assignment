# INTRODUCTION
Purpose of this module is to allow tracking JS errors via
Datadog service.

* Project page: coming soon

* To submit bug reports and feature suggestions, or to track changes: coming soon

# REQUIREMENTS
It requires only Drupal CORE.

# INSTALLATION
Install as any other contrib module, no specific configuration required for
installation.

# CONFIGURATION
* Visit /admin/config/system/datadog-js/settings
* Configure the token
* Configure if you want to track for admin pages too or not

# CONTEXTS
It is possible to override contexts by implementing an event listener like the example below:

```
// Add a new context to allow us to add a new column and filter by foo.
document.addEventListener('dataDogContextAlter', (e) => {
  const context = e.detail;
  // Add context for foo.
  context.foo = 'bar';
});
```
