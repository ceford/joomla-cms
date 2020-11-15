<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// Check if we have all the data
if (!key_exists('item', $displayData) || !key_exists('context', $displayData))
{
	return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item)
{
	return;
}

$context = $displayData['context'];

if (!$context)
{
	return;
}

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (key_exists('fields', $displayData))
{
	$fields = $displayData['fields'];
}
else
{
	$fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields))
{
	return;
}

$output = array();
$list_type = array();
$show_title = array();
$title_tag = array();
$title_class = array();

foreach ($fields as $field)
{
	// If the value is empty do nothing
	if (!isset($field->value) || trim($field->value) === '')
	{
		continue;
	}

	$class = $field->params->get('render_class');
	$layout = $field->params->get('layout', 'render');
	$content = FieldsHelper::render($context, 'field.' . $layout, array('field' => $field));

	// If the content is empty do nothing
	if (trim($content) === '')
	{
		continue;
	}

	if (isset($field->group_title))
	{
	    $gparams = json_decode($field->group_params);
	    $tag = ($gparams->render_tag == 'dl') ? 'dd' : 'li';
	    $list_type[$field->group_title] = $gparams->render_tag;
	    $list_type_class[$field->group_title] = $gparams->render_class;
	    $show_title[$field->group_title] = $gparams->show_title;
	    $title_tag[$field->group_title] = $gparams->title_tag;
	    $title_class[$field->group_title] = $gparams->title_class;
	    $output[$field->group_title][] = '<' . $tag  . ' class="field-entry ' . $class . '">' . $content . '</' . $tag  . '>';
	} else {
	    $list_type['none'] = 'ul';
	    $list_type_class['none'] = 'fields-container';
	    $show_title['none'] = 0;
	    $title_tag['none'] = '';
	    $title_class[none] = '';
	    $output['none'][] = '<li class="field-entry ' . $class . '">' . $content . '</li>';
	}
}

if (empty($output))
{
	return;
}

foreach ($list_type as $title => $list_type) 
{
    if ($show_title[$title] && $list_type != 'dl')
    {
        echo '<' . $title_tag[$title] . ' class="' . $title_class[$title] . '">' . $title . '</' . $title . '>' . "\n";
    }
    echo '<' . $list_type . ' class="' . $list_type_class[$title] . '">';
    if ($list_type == 'dl')
    {
        echo '<dt class="' . $list_type_class[$title] . '">' . $title . '</dt>' . "\n";
    }
    echo implode("\n", $output[$title]);
    echo '</' . $list_type . '>' . "\n";
}
