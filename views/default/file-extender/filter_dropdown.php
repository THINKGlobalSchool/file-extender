<?php
/**
 * File-Extender Filter Dropdown
 * 
 * @package File-Extender
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Jeff Tilson
 * @copyright THINK Global School 2010 - 2015
 * @link http://www.thinkglobalschool.com/
 * 
 * @see  input/dropdown
 * @uses $vars['label']
 */

echo "<div class='fileextender-filter-dropdown'>";
echo "<label>" . $vars['label'] . "</label>";
echo elgg_view('input/dropdown', $vars);
echo "</div>";
