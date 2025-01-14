<?php

/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $secondary_menu_heading: The title of the menu used by the secondary links.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['branding']: Items for the branding region.
 * - $page['header']: Items for the header region.
 * - $page['navigation']: Items for the navigation region.
 * - $page['help']: Dynamic help text, mostly for admin pages.
 * - $page['highlighted']: Items for the highlighted content region.
 * - $page['content']: The main content of the current page.
 * - $page['sidebar_first']: Items for the first sidebar.
 * - $page['sidebar_second']: Items for the second sidebar.
 * - $page['footer']: Items for the footer region.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 * @see omega_preprocess_page()
 */
$menu = render($page['menu']);
?>
<div class="l-page">
  <header class="l-header before" role="banner">
    <div class="l-header-inner">
      <div class="l-branding">
        <?php if ($logo): ?>
          <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="site-logo"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" /></a>
        <?php endif; ?>

        <?php print render($page['branding']); ?>
      </div>
  
      <div class="menubutton"><a class="mmenu-trigger"><i class="fa fa-bars"></i></a></div>
    </div>
  </header>


  <?php if ($page['slideshow']): ?>
  <?php print render($page['slideshow']); ?>
  <?php endif; ?>

  <header class="l-header after" role="banner">
    <div class="l-header-inner">
      <div class="l-branding">
        <?php if ($logo): ?>
          <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" class="site-logo"><img src="<?php print $logo; ?>" alt="<?php print t('Home'); ?>" /></a>
        <?php endif; ?>

        <?php print render($page['branding']); ?>
      </div>

      <?php print $menu; ?>
    </div>
  </header>

  <div class="l-main">
    <?php print $breadcrumb; ?>
    <div class="l-content" role="main" style="<?php print(empty($inlineStyles)) ?: implode('; ', $inlineStyles); ?>">
      <?php print render($page['highlighted']); ?>
      <a id="main-content"></a>
      <?php print render($title_prefix); ?>
      <?php if ($title): ?>
        <h1 id="page-title"><?php print $title; ?></h1>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php print $messages; ?>
      <?php print render($tabs); ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?>
        <ul class="action-links"><?php print render($action_links); ?></ul>
      <?php endif; ?>
      <?php if ($page['hero_left'] || $page['hero_right']): ?>
      <div class="hero">
        <?php print render($page['hero_right']); ?>
        <?php print render($page['hero_left']); ?>
      </div>
      <?php endif; ?>
      <?php print render($page['content']); ?>
      <?php if($page['blue']): ?>
      <?php print render($page['blue']); ?>
      <?php endif; ?>

      <?php if($page['white']): ?>
      <?php print render($page['white']); ?>
      <?php endif; ?>

      <?php if($page['black']): ?>
      <?php print render($page['black']); ?>
      <?php endif; ?>

      <?php if($page['white_two']): ?>
      <?php print render($page['white_two']); ?>
      <?php endif; ?>

      <?php if($page['blue_two']): ?>
      <?php print render($page['blue_two']); ?>
      <?php endif; ?>
      
      <?php if($page['green']): ?>
      <?php print render($page['green']); ?>
      <?php endif; ?>

      <?php if($page['grey']): ?>
      <?php print render($page['grey']); ?>
      <?php endif; ?>

       <?php if($page['dark_grey']): ?>
      <?php print render($page['dark_grey']); ?>
      <?php endif; ?>
      <?php print $feed_icons; ?>
    </div>

    <?php print render($page['side']); ?>
  </div>

  <footer class="l-footer" role="contentinfo">
    <?php print render($page['footer_first']); ?>
    <?php print render($page['footer_second']); ?>
  </footer>
</div>
