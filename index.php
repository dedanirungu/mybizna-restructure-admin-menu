<?php

/**
 * Mybizna Restructure Admin Menu
 *
 * @package           MybiznaRestructureAdminMenu
 * @author            Dedan Irungu
 * @copyright         2022 Mybizna.com
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Mybizna Restructure Admin Menu
 * Plugin URI:        https://wordpress.org/plugins/mybizna-restructure-admin-menu/
 * Description:       Mybizna Restructure Admin Menu.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.2
 * Author:            Dedan Irungu
 * Author URI:        https://mybizna.com
 * Text Domain:       mybizna-restructure-admin-menu
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

function _mybizna_menu_output($menu, $submenu, $submenu_as_parent = true)
{

    global $self, $parent_file, $submenu_file, $plugin_page, $typenow;

    $first = true;
    // 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes, 5 = hookname, 6 = icon_url.
    foreach ($menu as $key => $item) {
        $admin_is_parent = false;
        $class = array();
        $aria_attributes = '';
        $aria_hidden = '';
        $is_separator = false;

        if ($first) {
            $class[] = 'wp-first-item';
            $first = false;
        }

        $submenu_items = array();
        if (!empty($submenu[$item[2]])) {
            $class[] = 'wp-has-submenu';
            $submenu_items = $submenu[$item[2]];
        }

        if (($parent_file && $item[2] === $parent_file) || (empty($typenow) && $self === $item[2])) {
            if (!empty($submenu_items)) {
                $class[] = 'wp-has-current-submenu wp-menu-open';
            } else {
                $class[] = 'current';
                $aria_attributes .= 'aria-current="page"';
            }
        } else {
            $class[] = 'wp-not-current-submenu';
            if (!empty($submenu_items)) {
                $aria_attributes .= 'aria-haspopup="true"';
            }
        }

        if (!empty($item[4])) {
            $class[] = esc_attr($item[4]);
        }

        $class = $class ? ' class="' . implode(' ', $class) . '"' : '';
        $id = !empty($item[5]) ? ' id="' . preg_replace('|[^a-zA-Z0-9_:.]|', '-', $item[5]) . '"' : '';
        $img = '';
        $img_style = '';
        $img_class = ' dashicons-before';

        if (false !== strpos($class, 'wp-menu-separator')) {
            $is_separator = true;
        }

        /*
         * If the string 'none' (previously 'div') is passed instead of a URL, don't output
         * the default menu image so an icon can be added to div.wp-menu-image as background
         * with CSS. Dashicons and base64-encoded data:image/svg_xml URIs are also handled
         * as special cases.
         */
        if (!empty($item[6])) {
            $img = '<img src="' . $item[6] . '" alt="" />';

            if ('none' === $item[6] || 'div' === $item[6]) {
                $img = '<br />';
            } elseif (0 === strpos($item[6], 'data:image/svg+xml;base64,')) {
                $img = '<br />';
                $img_style = ' style="background-image:url(\'' . esc_attr($item[6]) . '\')"';
                $img_class = ' svg';
            } elseif (0 === strpos($item[6], 'dashicons-')) {
                $img = '<br />';
                $img_class = ' dashicons-before ' . sanitize_html_class($item[6]);
            }
        }
        $arrow = '<div class="wp-menu-arrow"><div></div></div>';

        $title = wptexturize($item[0]);

        // Hide separators from screen readers.
        if ($is_separator) {
            $aria_hidden = ' aria-hidden="true"';
        }

        echo "\n\t<li$class$id$aria_hidden>";

        if ($is_separator) {
            echo '<div class="separator"></div>';
        } elseif ($submenu_as_parent && !empty($submenu_items)) {
            $submenu_items = array_values($submenu_items); // Re-index.
            $menu_hook = get_plugin_page_hook($submenu_items[0][2], $item[2]);
            $menu_file = $submenu_items[0][2];
            $pos = strpos($menu_file, '?');

            if (false !== $pos) {
                $menu_file = substr($menu_file, 0, $pos);
            }

            if (!empty($menu_hook)
                || (('index.php' !== $submenu_items[0][2])
                    && file_exists(WP_PLUGIN_DIR . "/$menu_file")
                    && !file_exists(ABSPATH . "/wp-admin/$menu_file"))
            ) {
                $admin_is_parent = true;
                echo "<a href='admin.php?page={$submenu_items[0][2]}'$class $aria_attributes>$arrow<div class='wp-menu-image$img_class'$img_style aria-hidden='true'>$img</div><div class='wp-menu-name'>$title</div></a>";
            } else {
                echo "\n\t<a href='{$submenu_items[0][2]}'$class $aria_attributes>$arrow<div class='wp-menu-image$img_class'$img_style aria-hidden='true'>$img</div><div class='wp-menu-name'>$title</div></a>";
            }
        } elseif (!empty($item[2]) && current_user_can($item[1])) {
            $menu_hook = get_plugin_page_hook($item[2], 'admin.php');
            $menu_file = $item[2];
            $pos = strpos($menu_file, '?');

            if (false !== $pos) {
                $menu_file = substr($menu_file, 0, $pos);
            }

            if (!empty($menu_hook)
                || (('index.php' !== $item[2])
                    && file_exists(WP_PLUGIN_DIR . "/$menu_file")
                    && !file_exists(ABSPATH . "/wp-admin/$menu_file"))
            ) {
                $admin_is_parent = true;
                echo "\n\t<a href='admin.php?page={$item[2]}'$class $aria_attributes>$arrow<div class='wp-menu-image$img_class'$img_style aria-hidden='true'>$img</div><div class='wp-menu-name'>{$item[0]}</div></a>";
            } else {
                echo "\n\t<a href='{$item[2]}'$class $aria_attributes>$arrow<div class='wp-menu-image$img_class'$img_style aria-hidden='true'>$img</div><div class='wp-menu-name'>{$item[0]}</div></a>";
            }
        }

        if (!empty($submenu_items)) {
            echo "\n\t<ul class='wp-submenu wp-submenu-wrap'>";
            echo "<li class='wp-submenu-head' aria-hidden='true'>{$item[0]}</li>";

            $first = true;

            // 0 = menu_title, 1 = capability, 2 = menu_slug, 3 = page_title, 4 = classes.
            foreach ($submenu_items as $sub_key => $sub_item) {
                if (!current_user_can($sub_item[1])) {
                    continue;
                }

                $class = array();
                $aria_attributes = '';

                if ($first) {
                    $class[] = 'wp-first-item';
                    $first = false;
                }

                $menu_file = $item[2];
                $pos = strpos($menu_file, '?');

                if (false !== $pos) {
                    $menu_file = substr($menu_file, 0, $pos);
                }

                // Handle current for post_type=post|page|foo pages, which won't match $self.
                $self_type = !empty($typenow) ? $self . '?post_type=' . $typenow : 'nothing';

                if (isset($submenu_file)) {
                    if ($submenu_file === $sub_item[2]) {
                        $class[] = 'current';
                        $aria_attributes .= ' aria-current="page"';
                    }
                    // If plugin_page is set the parent must either match the current page or not physically exist.
                    // This allows plugin pages with the same hook to exist under different parents.
                } elseif (
                    (!isset($plugin_page) && $self === $sub_item[2])
                    || (isset($plugin_page) && $plugin_page === $sub_item[2]
                        && ($item[2] === $self_type || $item[2] === $self || file_exists($menu_file) === false))
                ) {
                    $class[] = 'current';
                    $aria_attributes .= ' aria-current="page"';
                }

                if (!empty($sub_item[4])) {
                    $class[] = esc_attr($sub_item[4]);
                }

                $class = $class ? ' class="' . implode(' ', $class) . '"' : '';

                $menu_hook = get_plugin_page_hook($sub_item[2], $item[2]);
                $sub_file = $sub_item[2];
                $pos = strpos($sub_file, '?');
                if (false !== $pos) {
                    $sub_file = substr($sub_file, 0, $pos);
                }

                $title = wptexturize($sub_item[0]);

                if (!empty($menu_hook)
                    || (('index.php' !== $sub_item[2])
                        && file_exists(WP_PLUGIN_DIR . "/$sub_file")
                        && !file_exists(ABSPATH . "/wp-admin/$sub_file"))
                ) {
                    // If admin.php is the current page or if the parent exists as a file in the plugins or admin directory.
                    if ((!$admin_is_parent && file_exists(WP_PLUGIN_DIR . "/$menu_file") && !is_dir(WP_PLUGIN_DIR . "/{$item[2]}")) || file_exists($menu_file)) {
                        $sub_item_url = add_query_arg(array('page' => $sub_item[2]), $item[2]);
                    } else {
                        $sub_item_url = add_query_arg(array('page' => $sub_item[2]), 'admin.php');
                    }

                    $sub_item_url = esc_url($sub_item_url);
                    echo "<li$class><a href='$sub_item_url'$class$aria_attributes>$title</a></li>";
                } else {
                    echo "<li$class><a href='{$sub_item[2]}'$class$aria_attributes>$title</a></li>";
                }
            }
            echo '</ul>';
        }
        echo '</li>';
    }
}

function mybizna_restructure_remove_submenus()
{
    global $menu;
    global $submenu;

    $admin_menu = array();
    $admin_submenu = array();

    if (isset($_SESSION['mybizna_admin_menu'])) {
        $admin_menu = $_SESSION['mybizna_admin_menu'];
    }

    $exemption = [
        'index.php', 'edit.php', 'plugins.php', 'users.php',
        'wpinv',
    ];

    foreach ($menu as $menu_key => $menu_item) {

        $item_name = (isset($menu_item[2])) ? $menu_item[2] : '';

        if ($item_name != '' && !in_array($item_name, $exemption)) {

            $admin_menu[$menu_key] = $menu_item;
            unset($menu[$menu_key]);

            if (isset($submenu[$item_name])) {
                $admin_submenu[$item_name] = $submenu[$item_name];
                unset($submenu[$item_name]);
            }

        }
    }


    $_SESSION['mybizna_admin_menu'] = $admin_menu;
    $_SESSION['mybizna_admin_submenu'] = $admin_submenu;
}

function mybizna_restructure_new_nav_menu()
{

    $admin_menu = $_SESSION['mybizna_admin_menu'];
    $admin_submenu = $_SESSION['mybizna_admin_submenu'];

    echo '<div id="mybizna_sidenav" class="mybizna_sidenav"
    <a href="javascript:void(0)" class="closebtn" onclick="mybizna_admin_sidebar_navigation()">&times;</a>
    <ul class="mybizna_menu">';
    _mybizna_menu_output($admin_menu, $admin_submenu);
    echo '</ul></div>
    <!-- Use any element to open the mybizna_sidenav -->
    <btn id="mybizna_sidenav_btn" class="mybizna_sidenav_btn button button-primary" onclick="mybizna_admin_sidebar_navigation()">More Menu</btn>
    ';

    if (is_admin()) {

        wp_register_style('mybizna_restructure_menu-css', plugins_url('assets/css/main.css', __FILE__));
        wp_enqueue_style('mybizna_restructure_menu-css');

        wp_register_script('mybizna_restructure_menu-js', plugins_url('assets/js/main.js', __FILE__));
        wp_enqueue_script('mybizna_restructure_menu-js');
    }
    //global $menu;
    //$menu[99] = array('', 'read', 'separator', '', 'menu-top menu-nav');
    //add_menu_page(__('Navigation', 'mav-menus'), __('Navigation', 'nav-menus'), 'edit_themes', 'nav-menus.php', '', '', 99);
}

function mybizna_restructure_init_session()
{
    if (!session_id()) {
        session_start();
    }
}

function mybizna_restructure_menu()
{

}

// Start session on init hook.
add_action('init', 'mybizna_restructure_init_session');
add_action('admin_footer', 'mybizna_restructure_new_nav_menu');
add_action('admin_menu', 'mybizna_restructure_remove_submenus', 10000000);
add_action('plugins_loaded', 'mybizna_restructure_menu');
