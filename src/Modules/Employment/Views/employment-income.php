<div class="whx4-employment">
	<p><strong>Employers: ....</strong> <?php //echo (int)$stats['monsters']; ?></p>
	<?php 
	foreach ($posts as $post) {
	    echo $post->post_title."<br />";
	}
	?>
	<p><strong>Debug:</strong> <pre><?php echo print_r($debug, true); ?></pre></p>
</div>