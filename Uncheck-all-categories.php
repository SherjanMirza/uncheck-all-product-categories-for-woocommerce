<?php
/**
 * Plugin Name: Uncheck All Product Categories
 * Description: Adds an "Uncheck all" button to the Product Categories box (Edit screen + Quick Edit + Bulk Edit).
 * Author: Sherjan Mirza
 * Author URI: https://github.com/sherjanmirza (or your website)
 * Version: 1.0.0
 * License: MIT
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Text Domain: uncheck-all-product-categories
 */
if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', function($hook){
    // Only load on product edit, product list
    $screen = get_current_screen();
    if (!$screen) return;
    if (!in_array($screen->id, ['product', 'edit-product'], true)) return;

    wp_register_script('uncheck-all-product-cats', false, ['jquery'], '1.0.0', true);
    wp_enqueue_script('uncheck-all-product-cats');

    $js = <<<'JS'
jQuery(function($){

  /* ========== Product Edit Screen (#product_catdiv) ========== */
  const $catBox = $('#product_catdiv');
  if ($catBox.length) {
    // Insert button into the metabox header
    const $btn = $('<a href="#" class="button button-small nx-uncheck-cats" style="margin-left:8px;">Uncheck all</a>');
    $catBox.find('.hndle').append($btn);

    $btn.on('click', function(e){
      e.preventDefault();
      $catBox.find('input[type="checkbox"]').prop('checked', false);
    });
  }

  /* ========== Helper to add button to any inline edit row (Quick/Bulk) ========== */
  function injectButtonIntoInlineEdit($ctx, label){
    if ($ctx.find('.nx-uncheck-cats-inline').length) return; // avoid dupes
    const $where = $ctx.find('.inline-edit-col-left, .inline-edit-col').first(); // stable spot
    if (!$where.length) return;

    const $wrap = $('<div class="nx-inline-tools" style="margin:6px 0;"></div>');
    const $btn  = $('<a href="#" class="button button-small nx-uncheck-cats-inline">'+label+'</a>');
    $wrap.append($btn);
    $where.prepend($wrap);

    $btn.on('click', function(e){
      e.preventDefault();
      // Uncheck ONLY product_cat in this context
      $ctx.find('input[type="checkbox"][name^="tax_input[product_cat]"]').prop('checked', false);
    });
  }

  /* ========== Quick Edit ========== */
  // When "Quick Edit" link is clicked, the row appears asynchronously.
  $(document).on('click', '.editinline', function(){
    setTimeout(function(){
      const $row = $('.inline-edit-row:visible');
      if ($row.length) injectButtonIntoInlineEdit($row, 'Uncheck all categories');
    }, 50);
  });

  /* ========== Bulk Edit ========== */
  // When "Bulk actions → Edit" is applied, the bulk row appears.
  // WP core uses #bulk-edit row; Woo keeps taxonomy checklist there too.
  const tryInjectBulk = function(){
    const $bulk = $('#bulk-edit:visible');
    if ($bulk.length) injectButtonIntoInlineEdit($bulk, 'Uncheck all categories');
  };

  // Inject when the bulk row is shown
  $(document).on('click', '#doaction, #doaction2', function(){
    setTimeout(tryInjectBulk, 150);
  });

  // Also try on list table redraws (filtering/paging)
  $(document).ajaxComplete(function(){
    setTimeout(tryInjectBulk, 150);
  });

});
JS;

    wp_add_inline_script('uncheck-all-product-cats', $js);
});
