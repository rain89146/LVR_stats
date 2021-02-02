<?php include 'header.php'; ?>
<div class="app-ui js-app-ui main-app-win">

    <!-- main content -->
    <main class="app-ui__body padding-md js-app-ui__body">
      <div class="margin-bottom-sm" style="display: flex; align-items: center;">
        <div id="global_tabbar_controller" style="width: 100%;"></div>
        <div style="margin-left: auto;padding-left:1rem;">
          <div id="global_freq_container"></div>
        </div>
      </div>
      
      <div class="margin-bottom-sm content-section" id="active_listing_summary"></div>
      <div class="margin-bottom-sm content-section" id="active_listing_no_offer_summary"></div>
      <div class="margin-bottom-sm content-section" id="new_listing_summary"></div>
      <div class="margin-bottom-sm content-section" id="sold_listing_summary_by_four"></div>
      <div class="margin-bottom-sm content-section" id="days_on_market_summary"></div>
      <div class="margin-bottom-sm content-section" id="sold_listing_city_overview_table"></div>
      <div class="margin-bottom-sm content-section" id="sold_listing_zip_overview_table"></div>
    </main>
  </div>

  <script src="./js/chart.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.css"></script>
  <script>AOS.init();</script>
  <script type="module" src="./js/Index/index.js"></script>
<?php  include 'footer.php'; ?>