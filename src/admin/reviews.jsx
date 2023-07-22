/**
 * Review request component.
 */

import admin from '../user-menus.php';

// if statement to check if 'ABSPATH' exists

/**
 * Function for Reviews
 */
export default function Reviews() {

    /**
	 * Tracking API Endpoint.
	 */
    // $api_url

    /**
	 * Init
	 */
    const init = () => {
        // add_action();
    }

    /**
	 * Hook into relevant WP actions.
	 */
    const hooks = () => {
        // if statement to check if the current user is admin.
        // add_action();
        // add_action();
        // add_action();
    }

    /**
     * Get the install date for comparisons. Sets the date to now if none is found.
     */
    const installed_on = () => {
        // $installed_on = get_option();

        // if statement to check if $installed_on is empty and returns the current time.

        // return $installed_on
    }

    /**
     * AJAX Handler
     */
    const ajax_handler = () => {
        
        // if statement to check if not wp_verify_nonce for $_REQUEST['nonce'] and returns wp_send_json_error().

        // $args = wp_parse_args();

        /**
         * try/catch with following:
         * 
         * try:
         * $user_id
         * 
         * $dismissed_triggers
         * 
         * update_user_meta()
         * 
         * update_user_meta() -- with current time in it.
         * 
         * switch statement ( $args['reason'] ) with  following:
         * 
         * case 'maybe_later' -- updates the update_user_meta and current_time
         * case 'am_now' -- nothing else for this one.
         * case 'already_did' -- sets already_did to true.
         * 
         * After switch statement:
         * wp_send_json_success(); -- ends try statement.
         * 
         * Starts catch statement:
         * wp_send_json_error(); -- ends catch statement.
         */
    }

    /**
     * Get review trigger group.
     */
    const get_trigger_group = () => {
        // $selected

        /**
         * if statement to check if $selected is not set
         * $dismissed_triggers
         * $triggers
         * 
         * foreach loop to check if $triggers is set and returns foreach loop $group['triggers'] as $t which returns the trigger. -- if statement to check if $trigger['conditions'] is not an array and if empty( $dismissed_triggers[$g] ) or $dismissed_triggers[$g] is less than $dismissed_triggers['pri'], then it returns $selected  -- first inner foreach closes.   --  if statement to check if $selected is set and returns break. 
         * End of these foreach loops.
         */

        // return $selected
    }

    /**
     * Get current trigger.
     */
    const get_current_trigger = () => {
        // $group = self::get_trigger_group();
        // $code  = self::get_trigger_code();

        // if statement to check if not $group or not $code then returns false.

        // $trigger = self::triggers( $group, $code );

        // if statement to check if the $key is empty return the $trigger -- elseif $trigger[$key] is set return it else return false.
    }

    /**
     * Returns an array of dismissed trigger groups.
     */
    const dismissed_triggers = () => {
        // $user_id = get_current_user_id();

        // $dismissed_triggers = get_user_meta();

        // if statement to check if $dismissed_triggers is empty and returns an empty array.

        // return $dismissed_triggers
    }

    /**
     * Returns true if the user has opted to never see this again. Or sets the option.
     */
    const already_did = ( set ) => {
        // $user_id = get_current_user_id();

        // if statement to check if $set is true and returns update_user_meta() then returns true.
        
        // return (bool) get_user_meta();
    }

    /**
     * Gets a list of triggers.
     */
    const triggers = ( group, code ) => {
        // $triggers;

        // if statement to check if $triggers is not set and returns $time_message, $triggers[].

        // $triggers = apply_filters();

        // Sort Groups.
        // usort();

        // Sort each groups triggers.
        // foreach loop to go through $triggers then usort().

        /**
         * if statement to check if $group is set
         * 
         * nested if statement to check if $triggeres[$group] is not set then returns false.
         * 
         * nested if statement to check if $code is not set then returns $triggers[$group] else returns an isset with if statement in it --  if $triggers[$group][$code] then $triggers[$group][$code] else false.
         */

        // return $triggers

    }

    /**
	 * Render admin notices if available.
	 */
    const admin_notices = () => {
        // if statement to check if there are hide_notices then returns.

        // $group   = self::get_trigger_group();
		// $code    = self::get_trigger_code();
		// $pri     = self::get_current_trigger( 'pri' );
		// $trigger = self::get_current_trigger();

        // $uuid

        // html
    }

    /**
     * Checks if notices should be shown.
     */
    const hide_notices = () => {
        // $code = self::get_trigger_code();

        // $conditions = [];

        // returns in_array();
    }

    /**
     * Gets the last dismissed date.
     */
    const last_dismissed = () => {
        // $user_id = get_current_user_id();

        // return get_user_meta();
    }

    /**
     * Sort array by priority value.
     */
    const sort_by_priority = ( $a, $b ) => {
        // if statement to check if $a['pri'] is not set or $b['pri'] is not set or if $a['pri'] === $b['pri'] returns 0.

        // return if statement to check if ( $a['pri'] < $b['pri'] ) then returns -1 else returns 1.
    }

    /**
     * Sort array in reverse by priority value
     */
    const rsort_by_priority = ( $a, $b ) => {
        // if statement to check if $a['pri'] is not set or $b['pri'] is not set or if $a['pri'] === $b['pri'] returns 0.

        // return if statement to check if ( $a['pri'] < $b['pri'] ) then returns 1 else returns -1.
    }
    
}

// Initialize Reviews
