Offlinequiz Cronjob Admin
================================

This file is part of the report_offlinequizcron plugin for Moodle - <http://moodle.org/>

*Author:*    Juergen Zimmer, Thomas Wedekind, Jakob Mischke
*Copyright:* [Academic Moodle Cooperation](http://www.academic-moodle-cooperation.org)
*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The Offlinequiz Cronjob Admin allows administrators to monitor and, if necessary, control important processes for the evaluation of offline quizzes in the backend of their Moodle platform.


Usage
-----------

Teachers have carried out an exam via the offline quiz activity. During the subsequent evaluation, several sheets were not evaluated correctly and the administrator of the Moodle platform was asked for assistance. The first step is to check the activity and identify the cause. For a quick correction, the offline quiz cronjob admin is now accessed and the details of the offline quiz evaluation are opened. The relevant scanned answer sheets can be downloaded collectively and uploaded again in the offline quiz for evaluation.


Requirements
------------

The module [mod_offlinequiz](https://moodle.org/plugins/mod_offlinequiz) must be installed to run this plugin.


Installation
------------

* Copy the code directly to the report/offlinequizcron directory.

* Log into Moodle as administrator.

* Open the administration area (http://your-moodle-site/admin) to start the installation
  automatically.


Privacy API
------------

The plugin fully implements the Moodle Privacy API.


Documentation
------------

You can find a documentation for the plugin on the [AMC website](https://academic-moodle-cooperation.org/report_offlinequizcron/).


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we cannot test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/academic-moodle-cooperation/moodle-report_offlinequizcron/issues). Please provide a detailed bug description, including the plugin and Moodle version and, if applicable, a screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
