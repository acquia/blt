# Open Source Contributions

##  What not to release
* Use-case specific functionality (i.e., stuff no one else will really care about)
    * When in doubt, release
* Confidential or security sensitive information
    * Definitions will depend upon applicable NDA or security agreements

## What to release

* Patches to core or contrib
    * Should always be contributed, if appropriate for public release
    * Make use of organization crediting
* Modules
    * Ensure that these are propery generalized / abstracted
    * Ensure that client information is removed from code

## Recommendation

An individual user (e.g., architect or developer) releases to drupal.org.

* Team should select who should own the module based on contribution to its 
  creation and abilty/interest in on-going ownership
* Ensure that maintainer transitioning is included in the process for 
      off-boarding of staff
* Make use of organization crediting
* Benefits
    * Greater community visibility 
    * Able to make use of D.O packaging and testing bots
* Drawbacks
    * organization does not have ownership of the module

### Alternatives

#### Organization ownership on D.O

Create a drupal.org account to represent the organization. Use this account to
maintain the project.

* Benefits
    * Organization cannot lose control of the module
    * Can serve NDA processes
* drawbacks
    * Organization users are not really supported on D.O; requires assistance from Drupal Association staff

#### Releasing on GitHub with D.O project page

* Benefits
    * Organization ownership is guaranteed
    * GitHub project flows (Pull Requests, etc.)
* Drawbacks
    * Frowned upon by D.O and part of community
- Considerations
    * should issues be handled on D.O, GitHub, or both?
        * recommendation: issues should be tracked on GitHub to integrate best with the workflow
