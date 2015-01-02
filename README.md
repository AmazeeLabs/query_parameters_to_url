# Query Parameters to Clean URLs

Introduction
------------
This module provides the ability to rewrite URL query parameters into Clean URL components on specified paths.

Motivation
----------
Views exposed filters generates URLs with multiple query parameters, and the URL path gets unwieldy
fast if there are multiple filters and filter values used. Furthermore because the path contains
query parameters, it might impact SEO results.

An example of a dirty URL like that could be:

[http://example-site.com/events?field_category_id[0]=100&field_category_id[1]=101&field_author_name[0]=John&field_author_surname[0]=Doe]
(http://example-site.com/events?field_category_id[0]=100&field_category_id[1]=101&field_author_name[0]=John&field_author_surname[0]=Doe)

Using this module you can transform the URL into:

[http://example-site.com/events/p/field_category_id/100--101/field_author_name/John/field_author_surname/Doe]
(http://example-site.com/events/p/field_category_id/100--101/field_author_name/John/field_author_surname/Doe)

Isn't that a joy to look at?

Required modules
----------------
No additional module dependencies, except having Clean URLs enabled.


Configuration
-------------
Configuration form can be found at Admin -> Configuration -> Search And metadata -> Query Parameters To URL settings.

(URL -> [/admin/config/search/query-parameters-to-url](/admin/config/search/query-parameters-to-url)).

The following configuration options are present:

* Allow disabling all path rewriting by un-checking checkbox.
* Allow configuring which characters should be used for delimiting query parameters. Care should be taken, to only use
 characters that are valid in an URI path component
 (refer to [RFC 3986](https://tools.ietf.org/html/rfc3986#section-3.3) for details).
* Allow setting a regular expression which is used to determine on which paths query parameter rewriting should occur.
* Additional rewrite-enabled paths can be added by implementing **hook_query_parameters_to_url_rewrite_access()**.


Usage
-----
Enable the module, go to the configuration form, configure on which paths should rewriting occur, 
using a regular expression.

Example regular expressions:

* "{}" or "{.+}" - Enable query parameter rewriting on all Drupal paths.
* "" (empty) - Disable query parameter rewriting on all Drupal paths.
* "{^events|^news}" - Enable query parameter rewriting on all paths that start with events or news.
* "{^node/([0-9]+)/(.+)}" - Enable query parameter rewriting on all node paths (view, edit, etc).


Implementation
--------------
The module uses three hooks to achieve its behavior:

* hook_url_outbound_alter().
* hook_url_inbound_alter().
* hook_init().

The first one is used to inspect links that go through l() or url(), check if they have query arguments, and rewrite the
url into a cleaner one.

The second one does the opposite, when a user accesses a URL in the browser, it checks if the url contains any encoded
query parameters, and sets them back into $_GET. Because some modules (Better Exposed Filters for example) 
don't use the $_GET variable directly, and instead use request_uri(), the new path is also set into 
$_SERVER['REQUEST_URI'], as well as a few more server global variables.

The last hook is the most important one, because it is the one that makes clean urls for Views Exposed Filters. Because
Views uses a <form> tag with action set to $_GET, there is no way to rewrite the URL, except maybe in Javascript. 
That's why in hook_init() we check whether the entered URL has query arguments, and if it does, we simply issue a
redirect to the Clean URL version (using a Location header).


Considerations
--------------
* Because the module uses the outbound hook, performance of the site can be slightly slower, depending on the number of
 links that will be rewritten. But it is a necessary slow-down.
* To speed up things a bit, you can disable "additional path" hook executions, so that only the regular expression is
 used.
* When nested query arguments arrays are used, like [(/events?a[0][1][2]=3&a[3]=4)](/events?a[0][1][2]=3&a[3]=4), 
 the resulting clean URL will be a bit less
 pretty because of the necessity for a proper encoding -> [(/events/p/a/0__1__2__3;3__4)](/events/p/a/0__1__2__3;3__4)

TODOs
-----
* Add ability to alter the parameters keys so they can be shorter.
* Add ability to alter the final encoded / decoded string that represents the query parameters.
