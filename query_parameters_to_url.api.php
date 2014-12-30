<?php
/**
 * @file
 * API documentation for Query Parameters to URL module.
 */

/**
 * Given path and contextual data, decide if path rewriting should be done.
 *
 * Might be called from hook_url_inbound_alter() or hook_url_outbound_alter().
 * Depending on that, passed contextual data can differ.
 *
 * @param $path String The path to rewrite.
 * @param $options Array An array of url options in outbound alter.
 * @param $original_path String|Null The original path before altering.
 * @param $path_language String|Null The path language in inbound alter.
 *
 * @return string
 */
function hook_query_parameters_to_url_rewrite_access($path, $options, $original_path, $path_language) {
  // Allow path rewriting on the special dashboard page.
  if ($path == 'special-dashboard') {
    return QUERY_PARAMETERS_TO_URL_REWRITE_ALLOW;
  }

  // Deny rewriting on the events page, if a special query parameter is set.
  if ($path == 'events' && isset($options['query']['special_parameter']) && $options['query']['special_argument'] == TRUE) {
    return QUERY_PARAMETERS_TO_URL_REWRITE_DENY;
  }

  // Ignore otherwise.
  return QUERY_PARAMETERS_TO_URL_REWRITE_IGNORE;
}