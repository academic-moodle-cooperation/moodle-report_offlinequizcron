@report @report_offlinequizcron @javascript
Feature: Within a moodle instance, an administrator should be able to have an overview of all evaluated jobs of scanned answer forms.
  In order to administrate scan jobs of the offline quiz
  As an administrator
  I need to access an overview of all jobs.

  Scenario: Switch as an admin to the report "Offlinequiz Cronjob Admin".

  Login as an admin, add a new offline quiz to a course and there some multiple choice questions. Then create the forms as PDF for the offline quiz.
    Given I log in as "admin"
    And I navigate to "Reports > Offlinequiz Cronjob Admin" in site administration
    Then I should see "List of offline quiz evaluation jobs"