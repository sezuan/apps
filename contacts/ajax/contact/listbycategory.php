<?php
/**
 * Copyright (c) 2011 Thomas Tanghus <thomas@tanghus.net>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

function cmpcategories($a, $b)
{
    if (strtolower($a['name']) == strtolower($b['name'])) {
        return 0;
    }
    return (strtolower($a['name']) < strtolower($b['name'])) ? -1 : 1;
}

function cmpcontacts($a, $b)
{
    if (strtolower($a['fullname']) == strtolower($b['fullname'])) {
        return 0;
    }
    return (strtolower($a['fullname']) < strtolower($b['fullname'])) ? -1 : 1;
}

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('contacts');

$offset = isset($_GET['startat']) ? $_GET['startat'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;

$list = array();

$catmgr = OC_Contacts_App::getVCategories();

if(is_null($category)) {
	$categories = $catmgr->categories(true);
	uasort($categories, 'cmpcategories');
	foreach($categories as $category) {
		$list[$category['id']] = array(
			'name' => $category['name'],
			'contacts' => $catmgr->itemsForCategory(
				$category['name'], 
				array(
					'tablename' => '*PREFIX*contacts_cards',
					'fields' => array('id', 'addressbookid', 'fullname'),
				),
				50, 
				$offset)
		);
		uasort($list[$category['id']]['contacts'], 'cmpcontacts');
	}
} else {
	$list[$category] = $catmgr->itemsForCategory(
		$category, 
		'*PREFIX*contacts_cards', 
		50, 
		$offset);
	uasort($list[$category], 'cmpcontacts');
}

session_write_close();

OCP\JSON::success(array('data' => array('categories' => $list)));