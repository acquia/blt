${project.human_name}

Drupal Architecture
========================
v1.0

Note: This is a living document that will evolve throughout the lifecycle of the project. Any delivery of this document is a snapshot in time and may be either incomplete or inaccurate as the project/platform evolves. This and related documents will be maintained throughout the project and the goal is to most accurately reflect the current known state of architecture for the various project components.

## Contents

- [Background](#background)
  - [Discovery](#discovery)
	- [Project Methodology](#project-methodology)
	- [Design](#design)
- [Technical Overview](#technical-overview)
  - [Infrastructure](#infrastructure)
      - [Environments](#environments)
      - [Traffic Management and CDN](#traffic-management-and-cdn)
  - [High-level Technical Requirements](#high-level-technical-requirements)
    - [Security](#security)
    - [Performance](#performance)
      - [Content Caching](#content-caching)
  - [Recommended practices](#recommended-practices)
    - [Performance profiling and testing](#performance-profiling-and-testing)
    - [Performance Configuration Review](#performance-configuration-review)
    - [Performance Testing](#performance-testing)
    - [Load and Stress testing](#load-and-stress-testing)
    - [Performance Goals](#performance-goals)
- [Development and deployment strategy](#development-and-deployment-strategy)
   - [Testing](#testing)
      - [QA Testing](#qa-testing)
     - [Performance and Load Testing](#performance-load)
- [User roles](#user-roles)
- [Custom and contributed projects](#custom-and-contributed-projects)
  - [Install Profile](#install-profile)
  - [Contributed Modules](#contributed-modules)
  - [Custom Modules](#custom-modules)
  - [Base Theme](#base-theme)
  - [Custom Themes](#custom-themes)
  - [Libraries](#libraries)
- [Content Architecture](#content-architecture)
  - [Article (content type)](#article)
   - [Features](#features)
   - [Assumptions](#assumptions)
   - [Risks](#risks)
   - [Examples](#examples)
  - [Tags (vocabulary)](#tags-vocabulary)
- [Features](#features)
  - [Feature A](#feature-a)
   - [Assumptions](#assumptions)
   - [Risks](#risks)
- [Theme Architecture](#theme-architecture)
- [Integrations Summary](#integrations-summary)
- [Migration](#migration)
   - [Assumptions](#assumptions)
   - [Risks](#risks)



#Background

##Discovery
TODO: Summary of discovery, findings, etc.

##Project methodology
TODO: Proposed or currently used sprint cycle overview, etc.

##Design
TODO: Who is doing design? List dependencies and known risks.


#Technical overview
TODO: High-level technical summary.

What is the main Drupal version (D7, D8)?
Is a distribution being used (Commons, Panelizer)?
Where is it hosted?

##Infrastructure
TODO: High-level overview of hosting infrastructure.

###Environments
TODO: Outline usage, audience, and any restrictions (IP or Authorization) of each environment.

| Environment | Short name | Audience                | Purpose                                |
|-------------|------------|-------------------------|----------------------------------------|
| Production  | prod       | Public                  | Public traffic                         |
| Staging     | test       | Client, Acquia, Partner | UAT                                    |
| Development | dev        | Acquia, Partner         | Code integration and developer testing |

###Traffic Management and CDN
TODO: Document any use of content delivery networks or other traffic management above the Acquia Cloud platform, including CDN content caching methodology (origin pull, push, both).

##High-level technical requirements
TODO: Browser requirements

###Security
TODO
- Security requirements
- Edit domain required?
- Which domain(s) run over SSL?
- IP-controlled white / black listing

###Performance
####Content Caching
TODO: Outline use of page/component caching with explanation of TTLs or any proactive cache clearing, including Drupal, Varnish, and CDN cache clearing.
##Recommended practices
###Performance profiling and testing
TODO
- Create component performance tracker.
- Create JMeter testing script and add it to the project repository.
- Write test scenarios with the client.

####Performance Configuration Review
At a high-level, there are many Drupal configuration options that can affect overall site performance. These settings are likely environment-specific and should be verified only against the production configuration, not local developer environments. Built-in and custom Insight tests can be used to track this data. Automated tools like Cache Audit can also be used locally for reviewing.

Examples include:

- Drupal performance settings (e.g. page cache lifetime, block caching, CSS/JS aggregation)
- Views cache settings (plugin, query, output)
- Panels display/pane cache settings (plugin, output)


####Performance Testing
The platform should be profiled and performance tested during development. This should include XHProf / New Relic code profiling, as well as site configuration and code review for potential issues. Load testing tools like JMeter can be used to simulate traffic to create results, but should not be relied upon to show any scalability issues at this point.

Because platforms are largely composed of sets of independent components, the combination available for building pages is extensive. This makes profiling of full pages largely unpredictable as pages are built and creates a need to identify performance levels on a component by component basis. Each component should be classified by the following characteristics:

- Baseline processing time, generated from the platform demo site
- Functional complexity
    - "Static" - renders without the need to gather external content
    - "Collection" - shows content external to the component, but defined by a specific list (rather than a query); for example, a list of one or many entity references
    - "Dynamic" - uses DB queries/cache requests to determine which content to show
    - "External" - uses an external web service, including Solr, to gather and show content
- Cache interval
    - "Never" - a new copy of the content is rendered within each request
    - "Time" - after a specific period of time, the content must be regenerated
		- "Content" - cache clearing responds to a specific event that is outside of this component


####Load and stress testing
Prior to public launch, the site(s) should be load and stress tested on the hosting cluster’s Staging environment to provide indications of bottlenecks.  Load and stress test need to occur on a timetable of no less than 2-3 weeks prior to launch in order to allow proper remediation of bottlenecks and tuning of hosting environments.

**The base platform should include a basic JMeter performance testing script which can be augmented with site-specific URLs (if relevant) and editorial process for authenticated traffic.** If further modes of testing are required (such as full browser emulation), additional tools or vendors may be required. If extensive AJAX / JS-related functionality exists, "real user" load testing is recommended over request-based tools like JMeter.

*Test scenarios should be made in collaboration with the client.* Using previous traffic analysis, typical user paths should be used for testing rather than a simple list of URLs. This more closely mimics real user traffic and also helps identify specific actions that may be taken, such as filling out forms or other interactions. It also better isolates where problems may exist and allow priorities to be made following test analysis.

The platform as a whole will be tested against total anticipated traffic numbers. Each site will require a new set of scenarios that will be run prior to launch and at each new platform launch. For example, in a multisite configuration, a launch of 4 sites will require 4 sets of scenarios and have the total traffic spread across 4 sites. At the next launch of 4 additional sites, 4 additional sets of scenarios will be created and the total load will be spread across all 8 existing sites.
Performance Goals
In order to quantify the pass / fail criteria for testing, specific goals must be established. These goals are not meant as a requirement of the platform, but will guide the pace at which new features are added if performance slows during development. The following performance metrics are the target for the platform:

- Total page views (per day/week/month)
   - XXX page views per hour, XXX peak
	 - XXX page views per day, XXX peak
- Average page load times (average / max / min)
- Number of concurrent users (max / min / average)
- Response time (time to first and last bytes)
- Requests per second (average / min / max)
- Editor experience: average / min / max time it takes to save an Article into the current CMS once the submit button is pressed.

#Development and deployment strategy
TODO: Compose a high-level summary of the development approach.

- Where is code hosted?
- What issue tracker is being used?
- What is the code review process?
- When will audits occur?
- Is continuous integration an element of the project? If so, describe it.
- How is configuration managed?
- What is the QA and regression testing process? Is there an automated testing strategy?
- What are the code quality and standards expectations? Coding standards, documentation expectations.
- Is this a multisite project? If so, describe the multisite update / rollout approach.
- Are there preconfigured Drush aliases? If so, note their location and configuration details.
- Are specific branches deployed to specific environments?


##Testing
####QA Testing
TODO: Outline testing standards, especially with regards to manual (test script-based) vs. automated.
####Performance and Load Testing
TODO: Outline overall need and acceptable performance metrics.


#User roles
TODO: Outline user roles and their permissions.

| Role name | Permissions (summary) |
|-----------|-----------------------|
| Administrator | 100% control over all aspects of the Drupal site. |
| Authenticated | - Can view published content.  - Can create forum posts. |
| Anonymous | Can view published content. |


#Custom and contributed projects
##Install profile
TODO: Install profile being used, if relevant.

##Contributed modules
TODO: A list of proposed or currently-used contrib modules (only if in addition to the install profile.
##Custom modules
TODO: An overview of custom modules, if relevant.
##Base theme
TODO: An overview of contrib base theme selection, if relevant.
##Custom themes
TODO: An overview of custom themes, if relevant.
#Libraries
TODO: Any libraries being used in the project.


#Content Architecture
TODO: This section should contain content types, taxonomy vocabularies, and other entity types / bundles, e.g. file types, custom entities, etc. It is assumed that not every entity type will need to list related features, assumptions, or risks.

##Article (content type)
TODO: A description of the entity type.

|Field | Type                     |  Notes |
|------|--------------------------|--------|
| Title | Text                    |        |
| Body  | Long text with summary  |        |  
| Image | Image                   |        |
| Tags  | To Tags vocabulary      |        |

###Features
TODO: Overview of functionality related to this content type.

###Assumptions
TODO: Assumptions related to this content type.

###Risks
TODO: Risks related to this content type.

Examples TODO: Examples of this content, if relevant.

##Tags (vocabulary)
TODO: A description of the entity type.

| Field       | Type                   | Notes                |
|-------------|------------------------|----------------------|
| Name        | Text                   |          -           |
| Description | Long text with summary |         -            |


Description
Long text with summary





#Features
TODO: A list of features that need to be built for this project. Should cover the relevant areas below.

If features are discussed but later removed from scope, do not simply delete them from the document. Instead, note in the feature description that the feature is now considered out of scope.

- Misc. integrations
- Internationalization / Localization
- Text formats and filters
- WYSIWYG
- Page building (Panels, Panelizer, Context, etc.)
- Workflow and moderation (Workbench, Organic groups)
- Administrative features
- Search strategy
- Media handling (images, video, documents, etc.)
- Analytics
- Ad provider integration
- Exposed web services
- Menu management
- Meta data and social integration (Metatags, etc.)
- URL structure

##Feature A
TODO: Feature description and notes.

###Assumptions
TODO: List of assumptions.

###Risks
TODO: List of risks.


#Theme architecture
TODO: Outline the general theme architecture.
- Base theme
- Page regions
- Template file naming
- CSS management (e.g. Compass)
- JS management
- Blocks and block management (if not mentioned earlier)


#Integrations summary
TODO: A summary of integration points, however slight they might be. Integration details, assumptions, and risks are likely already outlined in the Features section above. This section is purely for reference.

#Migration
TODO: If relevant, an high-level overview of data needing to be migrated and discussion of the migration strategy.


##Assumptions
TODO: Migration-related assumptions, e.g. delivery and access to data.
##Risks
TODO: Migration-related risks, e.g. delayed delivery of data.



