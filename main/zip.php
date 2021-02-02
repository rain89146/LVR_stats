<?php 
    include 'header.php';
    $zip = $_GET['zip'];
?>
<script> const ZIP = '<?php echo $zip ?>'; </script>
<script src='./js/Zip/index.js'></script>
<?php 
  include 'footer.php';
?>