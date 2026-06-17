<div class="row">
    <div class="col-md-12">
        <?php
        $announcements = getActiveAccouncements();
        foreach ($announcements as $announcement) {
            $color = $announcement->type->color;
        ?>
            <div class="alert text-black fade show" role="alert" style="background: <?= $color ?>">
                {{$announcement->body}}
            </div>
        <?php
        }
        ?>
    </div>
</div>
