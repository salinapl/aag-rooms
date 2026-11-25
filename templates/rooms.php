<h1><?= $page->title() ?></h1>
<ul>
    <?php foreach ($site->page('rooms')->children() as $link): ?>
        <li><a href="<?= $link->toUrl() ?>">Room: <?= $link->title() ?></a></li>
    <?php endforeach ?>
</ul>