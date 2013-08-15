<?php
    if ($notification['type'] == 'E') {
        $class = ' alert-error';
    } elseif ($notification['type'] = 'W') {
        $class = ' alert-success';
    } else {
        $class = '';
    }
?>
<div class="alert alert-block<?php echo $class; ?>">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <?php echo $notification['message']; ?>
</div>
