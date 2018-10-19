@regression
@ticket-BB-14735
@fixture-OroFrontendLocalizationBundle:translation-cache-for-multilanguage-application.yml
Feature: Translation cache for multilanguage application
  In order to use multilanguage application
  As a Buyer
  I want to see appropriate translations for each language without cache issues

  Scenario: Feature Background
    Given I login as administrator
    And I go to System/Configuration
    And I follow "System Configuration/General Setup/Localization" on configuration sidebar
    # we must enable new localization without translation cache clear
    When fill form with:
      | Enabled Localizations | [English, Zulu] |
      | Default Localization  | English         |
    And I submit form
    Then I should see "Configuration saved" flash message

  Scenario: Check Zulu on frontstore
    Given I signed in as AmandaRCole@example.org on the store frontend
    And I click "Localization Switcher"
    When I select "Zulu" localization
    Then I should see that the page does not contain untranslated labels
