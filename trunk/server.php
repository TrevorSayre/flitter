<?php
echo '<table style="border-collapse: collapse;">';
foreach ($_SERVER as $key => $val)
    echo '<tr onMouseOver="this.style.backgroundColor=\'AAAABB\';" onMouseOut="this.style.backgroundColor=\'transparent\';"><td style="font-weight: bold; border-right: 2px solid #000000;">'.$key.'</td><td style="width: 100%;">'.(is_array($val)?nl2br(print_r($val,true)):$val).'</td></tr>';
echo '</table>';
?>
