<?php
/*
Plugin Name: Category Monthly Archives
Version: 0.0.3
Description: This widget display monthly archive links on category archives page.
Author: Hiroshi Sawai
Author URI: http://www.info-town.jp
Plugin URI: http://www.creationlabs.net/category-monthly-archives
Text Domain: cmarchives
Domain Path: /languages
*/
/*  Copyright 2015  Hiroshi Sawai (email : info@info-town.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( dirname( __FILE__ ) . '/class.category-monthly-archives.php' );
add_action( 'widgets_init', 'cmarchives_widget_register' );
function cmarchives_widget_register() {
	register_widget( 'CategoryMonthlyArchives' );
}
