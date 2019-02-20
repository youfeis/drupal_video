CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Supporting Embedded Providers
 * Requirements
 * Recommended Modules
 * Installation
 * Configuration
 * FAQ
 * Maintainers


INTRODUCTION
------------

Video module allows you to embedded videos from YouTube and Vimeo (can be
extended to any provider) and upload videos and play using HTML5 video player
(can be extended).

 * For a full description of the module, visit the project page:
   https://www.drupal.org/project/video

 * To submit bug reports and feature suggestions, or to track changes:
   https://www.drupal.org/project/issues/video


SUPPORTING EMBEDDED PROVIDERS
-----------------------------

 * YouTube
 * Vimeo


REQUIREMENTS
------------

This module requires no modules outside of Drupal core.


RECOMMENDED MODULES
-------------------

 * Flysystem - https://www.drupal.org/project/flysystem


INSTALLATION
------------

 * Install the [insert name] module as you would normally install a
   contributed Drupal module. Visit
   https://www.drupal.org/node/1897420 for further information.


CONFIGURATION
-------------

    1. Navigate to Administration > Extend and enable the module.
    2. Navigate Administration > Structure > [Content type to edit] and add a
       field.
    3. Choose "Video" from Reference.
    4. Save and continue with the rest of the steps.
    5. Navigate to "Manage form display" and choose "Video Upload" widget.


FAQ
---
Can I upload multiple files to support HTML5 video?

- Yes, from "Manage fields" choose "Storage settings" and change the "Allowed
  number of values" to more than one. Then you can upload multiple videos like
  mp4, WebM etc.


MAINTAINERS
-----------

 * Heshan Wanigasooriya (heshanlk) - https://www.drupal.org/u/heshanlk
 * Jorrit Schippers (Jorrit) - https://www.drupal.org/u/jorrit
 * Fabio Varesano (fax8) - https://www.drupal.org/u/fax8

Supporting organizations:

 * Webotics.io - https://www.drupal.org/weboticsio
