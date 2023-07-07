<?php
/**
 * This file declares all of the plugin containers available services and accessors for IDEs to read.
 *
 * NOTE: VS Code can use this file as well when the PHP Intelliphense extension is installed to provide autocompletion.
 *
 * @package UserMenus\Plugin
 */

namespace PHPSTORM_META;

/**
 * Provide autocompletion for plugin container access.
 *
 * Return lists below all must match, it cannot be defined as a variable.
 * Thus all the duplication is needed.
 */

/**
  * NOTE: applies specifically to using the Plugin getter directly.
  * Example Usage: $events = pum_Scheduling_plugin()->get( 'events' );
  */
  override( \UserMenus\Plugin\Core::get(0), map( [
    // Controllers.
    ''             => '@',
    'connect'      => \UserMenus\Plugin\Connect::class,
    'license'      => \UserMenus\Plugin\License::class,
    'logging'      => \UserMenus\Plugin\Logging::class,
    'options'      => \UserMenus\Plugin\Options::class,
    'upgrader'     => \UserMenus\Plugin\Upgrader::class,
    'rules'        => \UserMenus\RuleEngine\Rules::class,
    'restrictions' => \UserMenus\Services\Restrictions::class,
  ] ) );

 /**
  * NOTE: applies specifically to using the global getter function.
  * Example Usage: $events = pum_scheduling( 'events' );
  */
  override ( \UserMenus\plugin(0), map( [
    // Controllers.
    '' => '@',
    'connect'      => \UserMenus\Plugin\Connect::class,
    'license'      => \UserMenus\Plugin\License::class,
    'logging'      => \UserMenus\Plugin\Logging::class,
    'options'      => \UserMenus\Plugin\Options::class,
    'upgrader'     => \UserMenus\Plugin\Upgrader::class,
    'rules'        => \UserMenus\RuleEngine\Rules::class,
    'restrictions' => \UserMenus\Services\Restrictions::class,
  ] ) );

  /**
  * NOTE: applies specifically to using the global getter function.
  * Example Usage: $events = pum_scheduling( 'events' );
  */
  override ( \UserMenus\Base\Container::get(0), map( [
    // Controllers.
    '' => '@',
    'connect'      => \UserMenus\Plugin\Connect::class,
    'license'      => \UserMenus\Plugin\License::class,
    'logging'      => \UserMenus\Plugin\Logging::class,
    'options'      => \UserMenus\Plugin\Options::class,
    'upgrader'     => \UserMenus\Plugin\Upgrader::class,
    'rules'        => \UserMenus\RuleEngine\Rules::class,
    'restrictions' => \UserMenus\Services\Restrictions::class,
  ] ) );

    /**
  * NOTE: applies specifically to using the global getter function.
  * Example Usage: $events = pum_scheduling( 'events' );
  */
override ( \UserMenus\Base\Container::offsetGet(0), map( [
  // Controllers.
  '' => '@',
  'connect'      => \UserMenus\Plugin\Connect::class,
  'license'      => \UserMenus\Plugin\License::class,
  'logging'      => \UserMenus\Plugin\Logging::class,
  'options'      => \UserMenus\Plugin\Options::class,
  'upgrader'     => \UserMenus\Plugin\Upgrader::class,
  'rules'        => \UserMenus\RuleEngine\Rules::class,
  'restrictions' => \UserMenus\Services\Restrictions::class,
] ) );
