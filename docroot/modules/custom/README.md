This directory should contain all custom modules and features.

CUSTOM OVERRIDES
----------------

Customizations/Overrides done for the core/contrib classes list here.

a. Override the core service 'router.route_provider' with class AlshayaDepartmentPageRouteProvider.
   @see AlshayaDepartmentPageServiceProvider for reference. This is done in order to replace term
   route with the node route for term page. For reference, see ticket ACR2-2310.

b. Override the core service 'router.no_access_checks' with class AlshayaDepartmentPageRouter.
   @see AlshayaDepartmentPageServiceProvider for reference. This is done in order to replace term
   route with the node route for term page and change the path inf. For reference, see ticket ACR2-2310.
