<?php
require 'php-backend/api/config.php';
$db = Database::getInstance()->getConnection();
$script = '<script type="text/javascript">var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();(function(){var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];s1.src="https://embed.tawk.to/default/default";s1.charset="UTF-8";s1.setAttribute("crossorigin","*");s0.parentNode.insertBefore(s1,s0);})();</script>';
$stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, setting_group) VALUES ('footer_scripts', ?, 'footer') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$stmt->execute([$script]);
echo 'Tawk.to script added to settings table.';
