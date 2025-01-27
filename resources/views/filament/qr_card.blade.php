<?php
use LaraZeus\Qr\Facades\Qr;
?>
<div class="p-3">
    {!!Qr::render($record->qr_code)!!}
</div>
