<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div style="background: yellow; padding: 50px; margin: 20px; border: 5px solid red;">
    <h1 style="color: red; font-size: 60px;">🎉 MEDIA MANAGER TEMPLATE TEST 🎉</h1>
    <p style="font-size: 30px;">Si vous voyez ceci, le template se charge correctement !</p>

    <h2>Informations de debug :</h2>
    <ul style="font-size: 20px;">
        <li><strong>User ID:</strong> <?php echo get_current_user_id(); ?></li>
        <li><strong>Est vendor:</strong> <?php echo el_is_vendor() ? 'OUI ✅' : 'NON ❌'; ?></li>
        <li><strong>Template path:</strong> <?php echo __FILE__; ?></li>
    </ul>

    <h2>Classes chargées :</h2>
    <ul style="font-size: 18px;">
        <li>EL_Vendor_Folders: <?php echo class_exists('EL_Vendor_Folders') ? '✅ Chargée' : '❌ Non chargée'; ?></li>
        <li>EL_Vendor_Media_Manager: <?php echo class_exists('EL_Vendor_Media_Manager') ? '✅ Chargée' : '❌ Non chargée'; ?></li>
        <li>EL_Vendor_Media_Ajax: <?php echo class_exists('EL_Vendor_Media_Ajax') ? '✅ Chargée' : '❌ Non chargée'; ?></li>
    </ul>
</div>
