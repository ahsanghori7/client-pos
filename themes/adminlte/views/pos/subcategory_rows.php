<?php if($rows): ?>
	<?php foreach($rows as $category): ?>
	<a id="category_btn" class="btn btn-app table_cat" data-name="<?= $category->name;?>" onclick="selectCategory(<?= $category->id; ?>, <?= ((int)$category->parent_id) == 0 ? 'true': 'false'; ?>)">
	    <div style="background-image:url(<?= base_url() ?>assets/uploads/<?= $category->image ? $category->image : 'no_image.png'; ?>); background-size: 100px 100px;  height: 100px;width: 100px;">
	    </div>
	    <span style="font-size: 12px" id="cat_id_<?= $category->id; ?>" class="label label-warning label-btnpr"><?= $category->name;?></span>    
	</a>
	<?php endforeach; ?>
<?php endif; ?>
<hr>

<div class="quick-menu">
    <div id="proContainer">
        <div id="ajaxproducts">
            <div id="item-list">
                <?php echo $products; ?>
            </div>
        </div>
        <div style="clear:both;"></div>
    </div>
</div>
