<?php

// Avoid direct access to this piece of code
if (!function_exists('add_action')) exit;

// Define the tabs
$tabs = '';
$current_tab = empty($_GET['tab']) ? 1 : intval($_GET['tab']);
$config_tabs = apply_filters('power_stats_config_tabs', array(__('General', 'wp-power-stats'), __('Exclusions', 'wp-power-stats'), __('Permissions', 'wp-power-stats'), __('Advanced', 'wp-power-stats')));
foreach ($config_tabs as $a_tab_id => $a_tab_name) {
    if ($a_tab_name != 'none') $tabs .= '<a href="'. PowerStatsAdmin::$config_url.($a_tab_id+1) .'" class="nav-tab' . (($current_tab == $a_tab_id + 1) ? ' nav-tab-active' : '') . '">' .$a_tab_name . "</a>\n";
}

echo '<div class="wrap"><h2 class="nav-tab-wrapper">' . $tabs . '</h2>';

$options_on_this_page = array();
switch ($config_tabs[$current_tab - 1]) {
    case __('General', 'wp-power-stats'):
        $options_on_this_page = array(
            'tracker_active' => array('description' => __('Tracking', 'wp-power-stats'), 'type' => 'yesno', 'custom_label_yes' => 'Enabled', 'custom_label_no' => 'Disabled', 'long_description' => __('Status of WP Power Stats statistics tracker. If disabled, you will be able to view the statistics, but no new statistics will be collected.', 'wp-power-stats')),
            'track_users' => array('description' => __('Track Registered Users', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __('Enable this option to track logged in users.', 'wp-power-stats')),
            'auto_purge' => array('description' => __('Statistics Period', 'wp-power-stats'), 'type' => 'integer', 'long_description' => __("Delete statistics older than the number of days specified. Enter <strong>0</strong> (number zero) for unlimited.", 'wp-power-stats') . (wp_get_schedule('power_stats_purge') ? ' <br> ' . __('Next clean-up on', 'wp-power-stats') . ' ' . date_i18n(get_option('date_format') . ', ' . get_option('time_format'), wp_next_scheduled('power_stats_purge')) . '. ' . sprintf(__('Entries logged on or before %s will be permanently deleted.', 'wp-power-stats'), date_i18n(get_option('date_format'), strtotime('-' . PowerStats::$options['auto_purge'] . ' days'))) : ''), 'after_input_field' => __('days', 'wp-power-stats')),
            'dashboard_widget' => array('description' => __('Dashboard Widget', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __('Enable this option to add a widget to the dashboard.', 'wp-power-stats')),
            'replace_dashboard' => array('description' => __('Replace Dashboard', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __('Enable this option to replace the default WordPress dashboard with WP Power Stats overview page.', 'wp-power-stats')),
        );

        // If auto purge is disabled, remove the cron job. If auto purge setting is on, but no cron job initialized, add it
        if (isset($_POST['options']['auto_purge'])) {
            if ($_POST['options']['auto_purge'] == 0) {
                wp_clear_scheduled_hook('power_stats_purge');
            } else if (wp_next_scheduled('power_stats_purge') == 0) {
                wp_schedule_event(time(), 'daily', 'power_stats_purge');
            }
        }
        break;
    case __('Exclusions', 'wp-power-stats'):
        $options_on_this_page = array(
            'ignore_bots' => array('description' => __('Bots Exclusion', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __("Do not to track visits from bots and search engines crawlers.", 'wp-power-stats')),
            'ignore_spam_visitors' => array('description' => __('Spam Exclusion', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __("Do not track visits from users who left a spam comment. This option requires Akismet plugin to be enabled. Page views generated by users whose comments are later marked as spam, will also be removed from the statistics.", 'wp-power-stats')),
            'ignore_do_not_track' => array('description' => __('Do Not Track Exclusion', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __("Do not track visitors who have the Do Not Track settings enabled in their browser.", 'wp-power-stats')),
            'ignore_admin_pages' => array('description' => __('Administration Exclusion', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __("Do not track activity within your WordPress administration panels.", 'wp-power-stats')),
            'ignore_ip' => array('description' => __('IP Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("List all the IP addresses you don't want to track, separated by commas. Each network <strong>must</strong> be defined using the <a href='http://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing' target='_blank'>CIDR notation</a> (i.e. <em>192.168.0.0/24</em>). This filter applies both to the public IP and the originating IP, if available.", 'wp-power-stats')),
            'ignore_browser' => array('description' => __('User Agent Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("Browsers (user agents) you don't want to track, separated by commas. You can specify the browser's version adding a slash after the name  (i.e. <em>Firefox/3.6</em>). Wildcards: <code>*</code> matches 'any string, including the empty string', <code>!</code> matches 'any character'. For example, <code>Chr*</code> will match Chrome and Chromium, <code>IE/!.0</code> will match IE/7.0 and IE/8.0. Strings are case-insensitive.", 'wp-power-stats')),
            'ignore_country' => array('description' => __('Country Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("Country codes (i.e.: <code>en-us, it, es</code>) that you don't want to track, separated by commas.", 'wp-power-stats')),
            'ignore_referrer' => array('description' => __('Referral Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("Referring URLs that you don't want to track, separated by commas: <code>http://mysite.com*</code>, <code>*/ignore-me-please</code>, etc. Wildcards: <code>*</code> matches 'any string, including the empty string', <code>!</code> matches 'any character'. Strings are case-insensitive. Please include either a wildcard or the protocol you want to filter (http://, https://).", 'wp-power-stats')),
            'ignore_user' => array('description' => __('User Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("List all the usernames you don't want to track, separated by commas. Please be aware that spaces are <em>not</em> ignored and that usernames are case sensitive.", 'wp-power-stats'), 'skip_update' => true),
            'ignore_pages' => array('description' => __('Page Exclusion', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __("List all the URLs on your website that you don't want to track, separated by commas. Don't include the domain name: <em>/about, ?p=1</em>, etc. Wildcards: <code>*</code> matches 'any string, including the empty string', <code>!</code> matches 'any character'. For example, <code>/abou*</code> will match /about and /abound, <code>/abo*t</code> will match /aboundant and /about, <code>/abo!t</code> will match /about and /abort. Strings are case-insensitive.", 'wp-power-stats')),
        );

        // Some options need a special treatment
        if (isset($_POST['options'])) {
            if (!empty($_POST['options']['ignore_user'])) {
                // Make sure all the users exist in the system
                $user_array = PowerStats::string_to_array($_POST['options']['ignore_user']);
                $post_data = trim($_POST['options']['ignore_user']);

                if (is_array($user_array) && !empty($post_data)) {
                    $sql_user_placeholders = implode(', ', array_fill(0, count($user_array), '%s'));
                    if ($GLOBALS['wpdb']->get_var($GLOBALS['wpdb']->prepare("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->users} WHERE user_login IN ($sql_user_placeholders)", $user_array)) == count($user_array)) {
                        PowerStats::$options['ignore_user'] = $_POST['options']['ignore_user'];
                    } else {
                        PowerStatsAdmin::$faulty_fields[] = __('some users in "User Exclusion" were not found', 'wp-power-stats');
                    }
                }
            } else {
                PowerStats::$options['ignore_user'] = '';
            }
        }
        break;
    case __('Permissions', 'wp-power-stats'):
        $options_on_this_page = array(
            'view_roles' => array('description' => __('Statistics View Roles', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __('List all the roles, separated by a comma, to allow access to the statistics. Administrator role is always permitted.', 'wp-power-stats')),
            'admin_roles' => array('description' => __('Configuration Roles', 'wp-power-stats'), 'type' => 'textarea', 'long_description' => __('List all the roles, separated by a comma, to allow access to modify the settings. Administrator role is always permitted.', 'wp-power-stats')),
        );

        // Some options need a special treatment
        if (isset($_POST['options'])) {
            if (!empty($_POST['options']['view_roles'])) {
                // Make sure all the entered roles exist
                $role_array = PowerStats::string_to_array(strtolower($_POST['options']['view_roles']));
                $role_not_found = false;
                foreach ($role_array as $index => $role) {
                    // get the role object
                    $obj_role = get_role($role);
                    if ($obj_role === null) $role_not_found = true;
                }
                // All entered roles are valid
                if (!$role_not_found) {
                    // Remove the capability from all roles
                    foreach ($wp_roles->roles as $role) {
                        $role = get_role(strtolower($role['name']));
                        $role->remove_cap('wp_power_stats_view');
                    }
                    // Add the capability to the entered roles
                    foreach ($role_array as $role) {
                        // get the role object
                        $obj_role = get_role($role);
                        // add the custom capability to this role object
                        $obj_role->add_cap('wp_power_stats_view');
                    }
                } else {
                    PowerStatsAdmin::$faulty_fields[] = __('some roles in "Statistics View Roles" were not found', 'wp-power-stats');
                }
            } else {
                // Remove the capability from all roles
                foreach ($wp_roles->roles as $role) {
                    $role = get_role(strtolower($role['name']));
                    $role->remove_cap('wp_power_stats_view');
                }
                PowerStats::$options['view_roles'] = '';
            }
            if (!empty($_POST['options']['admin_roles'])) {
                // Make sure all the entered roles exist
                $role_array = PowerStats::string_to_array(strtolower($_POST['options']['admin_roles']));
                $role_not_found = false;
                foreach ($role_array as $index => $role) {
                    // get the role object
                    $obj_role = get_role($role);
                    if ($obj_role === null) $role_not_found = true;
                }
                // All entered roles are valid
                if (!$role_not_found) {
                    // Remove the capability from all roles
                    foreach ($wp_roles->roles as $role) {
                        $role = get_role(strtolower($role['name']));
                        $role->remove_cap('wp_power_stats_admin');
                    }
                    // Add the capability to the entered roles
                    foreach ($role_array as $role) {
                        // get the role object
                        $obj_role = get_role($role);
                        // add the custom capability to this role object
                        $obj_role->add_cap('wp_power_stats_admin');
                    }
                } else {
                    PowerStatsAdmin::$faulty_fields[] = __('some roles in "Statistics Admin Roles" were not found', 'wp-power-stats');
                }
            } else {
                // Remove the capability from all roles
                foreach ($wp_roles->roles as $role) {
                    $role = get_role(strtolower($role['name']));
                    $role->remove_cap('wp_power_stats_admin');
                }
                PowerStats::$options['admin_roles'] = '';
            }
        }
        break;
    case __('Advanced', 'wp-power-stats'):
        $options_on_this_page = array(
            'session_duration' => array('description' => __('Visit Duration', 'wp-power-stats'), 'type' => 'integer', 'long_description' => __('Period of time, during which requests from the same uniquely identified client are considered a unique visit.', 'wp-power-stats'), 'after_input_field' => __('seconds', 'wp-power-stats')),
            'extend_session' => array('description' => __('Extend Visit', 'wp-power-stats'), 'type' => 'yesno', 'long_description' => __('Extend the duration of a visit each time the user visits a new page.', 'wp-power-stats')),
        );
        break;
    default:
        break;
}

if (has_filter('power_stats_options_on_page') && $config_tabs[$current_tab - 1] == __('Add-ons', 'wp-power-stats')) {
    $options_on_this_page = apply_filters('power_stats_options_on_page', $options_on_this_page);
}

if (isset($options_on_this_page)) {
    PowerStatsAdmin::update_options($options_on_this_page);
    PowerStatsAdmin::display_options($options_on_this_page, $current_tab);
}
echo '</div>';
