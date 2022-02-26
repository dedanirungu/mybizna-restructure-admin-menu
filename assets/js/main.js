var mybizna_admin_sidebar_width = 0;

/* Set the width of the side navigation to 250px and the left margin of the page content to 250px */
function mybizna_admin_sidebar_navigation() {

    mybizna_admin_sidebar_width = (mybizna_admin_sidebar_width) ? 0 : 250;

    document.getElementById("mybizna_sidenav").style.width = mybizna_admin_sidebar_width + 'px';
    document.getElementById("mybizna_sidenav_btn").style.right = mybizna_admin_sidebar_width + 'px';
}

jQuery(document).ready(function () {
    jQuery('.wp-has-submenu').hover(function () {
        jQuery(this).toggleClass('opensub');
    });
});