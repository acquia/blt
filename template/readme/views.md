# Views

## Building Views

The following are guidelines for building new views.

* _Caching_ 
  * For all public facing views, always use some form of caching. A non time-based cache is preferable because it often permits the longest cache lifetimes, and invalidates caches only when necessary. Possible options are:
    * [Views content cache](https://www.drupal.org/project/views_content_cache)
    * [Views argument cache](https://www.drupal.org/project/views_arg_cache)
    * Time based
* _Pagination_
  * Always specify either a fixed number of results or a pager. Never display all results. 
  * Whenever possible, use Views lite pager rather than a full pager. 
    * Note that this is unfortunately mutually exclusive with caching due to bugs in Views Lite Pager. @see https://www.drupal.org/node/2285591.
* _Advanced_
  * Always add a semantically descriptive machine name to views displays. E.g., use `press_releases_all` rather than `page_1`. This makes all PHP, CSS, and JS related to the view display more readable.
* _Relationships_
  * Whenever possible, use "require this relationship" for all views relationships. This causes views to perform an inner join rather than a left join, and is almost always faster.
  * Never use the `Taxonomy Terms on Node` relationship or filter. Instead, use a relationship or filter for the specific taxonomy reference field. This has a tremendous performance impact.
* _Filters_ 
  * For all exposed filters, manually set the fitler identifier to something end-user friendly. This should not contain drupalisms. E.g., use `type` rather than `field_type`.
* _No Results Behavior_
  * Always add "No Results behavior" of some type. This is typically text informing that user that no results were found.

@todos:

* When to ditch Views in favor of EFQ or straight queries. There's a reason Advanced Forum is slow on big sites.
* Proper views naming and tagging
