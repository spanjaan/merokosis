<div class="field-repeater"
    data-control="fieldrepeater"
    <?= $titleFrom ? 'data-title-from="'.$titleFrom.'"' : '' ?>
    <?= $minItems ? 'data-min-items="'.$minItems.'"' : '' ?>
    <?= $maxItems ? 'data-max-items="'.$maxItems.'"' : '' ?>
    <?= $style ? 'data-style="'.$style.'"' : '' ?>
    <?php if ($sortable) : ?>
    data-sortable="true"
    data-sortable-container="#<?= $this->getId('items') ?>"
    data-sortable-handle=".<?= $this->getId('items') ?>-handle"
    <?php endif; ?>
>
    <ul id="<?= $this->getId('items') ?>" class="field-repeater-items">
        <?php foreach ($formWidgets as $index => $widget) : ?>
            <?= $this->makePartial('repeater_item', [
                'widget' => $widget,
                'indexValue' => $index
            ]) ?>
        <?php endforeach ?>
    </ul>

    <?php if (!$this->previewMode) : ?>
        <div class="field-repeater-add-item loading-indicator-container indicator-center">
            <?php if ($useGroups) : ?>
                <a
                    href="javascript:;"
                    data-repeater-add-group
                    data-load-indicator>
                    <?= e(trans($prompt)) ?>
                </a>
            <?php else : ?>
                <a
                    href="javascript:;"
                    data-repeater-add
                    data-request="<?= $this->getEventHandler('onAddItem') ?>"
                    data-load-indicator>
                    <?= e(trans($prompt)) ?>
                </a>
            <?php endif ?>
        </div>

        <input type="hidden" name="<?= $this->alias; ?>_loaded" value="1">
    <?php endif ?>

    <script type="text/template" data-group-palette-template>
        <div class="popover-head">
            <h3><?= e(trans($prompt)) ?></h3>
            <button type="button" class="close"
                data-dismiss="popover"
                aria-hidden="true">&times;</button>
        </div>
        <div class="popover-fixed-height w-300">
            <div class="control-scrollpad" data-control="scrollpad">
                <div class="scroll-wrapper">
                    <div class="control-filelist filelist-hero" data-control="filelist">
                        <ul>
                            <?php foreach ($groupDefinitions as $item) : ?>
                                <li>
                                    <a
                                        href="javascript:;"
                                        data-repeater-add
                                        data-request="<?= $this->getEventHandler('onAddItem') ?>"
                                        data-request-data="_repeater_group: '<?= $item['code'] ?>'">
                                        <i class="list-icon <?= $item['icon'] ?>"></i>
                                        <span class="title"><?= e(trans($item['name'])) ?></span>
                                        <span class="description"><?= e(trans($item['description'])) ?></span>
                                        <span class="borders"></span>
                                    </a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </script>
</div>
